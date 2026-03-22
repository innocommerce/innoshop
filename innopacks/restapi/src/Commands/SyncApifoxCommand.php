<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class SyncApifoxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apifox:sync
        {--type= : Sync type: front, panel, or all (default: all)}
        {--token= : Apifox API token (or set APIFOX_TOKEN env)}
        {--front-project= : Front API project ID (or set APIFOX_FRONT_PROJECT_ID env)}
        {--panel-project= : Panel API project ID (or set APIFOX_PANEL_PROJECT_ID env)}
        {--keep-unmatched : Do not delete Apifox endpoints/schemas missing from the OpenAPI file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync OpenAPI specs to Apifox (generate docs and upload)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type           = $this->option('type') ?? 'all';
        $token          = $this->option('token') ?? env('APIFOX_TOKEN');
        $frontProjectId = $this->option('front-project') ?? env('APIFOX_FRONT_PROJECT_ID');
        $panelProjectId = $this->option('panel-project') ?? env('APIFOX_PANEL_PROJECT_ID');
        $keepUnmatched  = $this->option('keep-unmatched')
            || filter_var(env('APIFOX_KEEP_UNMATCHED', false), FILTER_VALIDATE_BOOLEAN);

        if (! $token) {
            $this->error('APIFOX_TOKEN is required. Set it in .env or pass --token option.');

            return self::FAILURE;
        }

        if ($keepUnmatched) {
            $this->comment('Apifox: keeping resources not listed in OpenAPI (--keep-unmatched / APIFOX_KEEP_UNMATCHED).');
        } else {
            $this->comment('Apifox: sync mode — unmatched resources removed when the API accepts deleteUnmatchedResources.');
        }

        // Generate and sync based on type
        $types = ($type === 'all') ? ['front', 'panel'] : [$type];

        foreach ($types as $t) {
            $this->info("Processing {$t} API...");

            $configName  = ($t === 'panel') ? 'scribe_panel' : 'scribe';
            $openapiFile = storage_path('app/'.($t === 'panel' ? 'scribe_panel' : 'scribe').'/openapi.yaml');
            $projectId   = ($t === 'panel') ? $panelProjectId : $frontProjectId;

            // Generate docs
            $this->info("Generating {$t} API docs...");
            $this->call('scribe:generate', ['--config' => $configName]);

            if (! file_exists($openapiFile)) {
                $this->error("OpenAPI file not found: {$openapiFile}");

                return self::FAILURE;
            }

            $this->info("OpenAPI spec generated: {$openapiFile}");

            // Sync to Apifox
            if (! $projectId) {
                $this->warn("Skipping {$t} API sync: project ID not set");

                continue;
            }

            $this->info("Syncing {$t} API to Apifox (Project: {$projectId})...");

            try {
                $result = $this->uploadToApifox($openapiFile, (string) $projectId, $token, $keepUnmatched);

                if ($result['success']) {
                    $this->info("{$t} API synced successfully!");
                    if (isset($result['counters']) && is_array($result['counters'])) {
                        $this->displayCounters($result['counters'], $t);
                        $this->warnIfNoEndpointsImported($result['counters'], $t);
                    } else {
                        $this->warn("{$t}: Apifox response had no counters; use -v to inspect. Confirm project ID and API base URL.");
                    }
                    if ($this->output->isVerbose() && isset($result['raw'])) {
                        $this->line(json_encode($result['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }
                } else {
                    $this->error("Failed to sync {$t} API: {$result['error']}");
                }
            } catch (ConnectionException $e) {
                $this->error("Connection error: {$e->getMessage()}");

                return self::FAILURE;
            }
        }

        $this->info('Done!');

        return self::SUCCESS;
    }

    /**
     * Upload OpenAPI spec to Apifox.
     *
     * Apifox expects `input` to be a JSON **string** containing the OpenAPI object (same as
     * `JSON.stringify(spec)` in their examples). Sending YAML inside `input.data` is not applied.
     *
     * @param  bool  $keepUnmatched  Skip deleteUnmatchedResources and keep schemas when true.
     */
    protected function uploadToApifox(string $filePath, string $projectId, string $token, bool $keepUnmatched = false): array
    {
        try {
            $spec = Yaml::parseFile($filePath);
        } catch (ParseException $e) {
            return [
                'success' => false,
                'error'   => 'Invalid OpenAPI YAML: '.$e->getMessage(),
            ];
        }

        if (! is_array($spec)) {
            return [
                'success' => false,
                'error'   => 'OpenAPI YAML did not parse to an object.',
            ];
        }

        try {
            $input = json_encode($spec, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $e) {
            return [
                'success' => false,
                'error'   => 'OpenAPI JSON encode failed: '.$e->getMessage(),
            ];
        }

        $options = [
            'endpointOverwriteBehavior'     => 'OVERWRITE_EXISTING',
            'schemaOverwriteBehavior'       => $keepUnmatched ? 'KEEP_EXISTING' : 'OVERWRITE_EXISTING',
            'updateFolderOfChangedEndpoint' => true,
        ];

        if (! $keepUnmatched) {
            $options['deleteUnmatchedResources'] = true;
        }

        $baseUrl = rtrim((string) (env('APIFOX_API_BASE_URL') ?: 'https://api.apifox.com'), '/');

        $response = Http::withHeaders([
            'X-Apifox-Api-Version' => '2024-03-28',
            'Authorization'        => 'Bearer '.$token,
            'Content-Type'         => 'application/json',
        ])->post("{$baseUrl}/v1/projects/{$projectId}/import-openapi", [
            'input'   => $input,
            'options' => $options,
        ]);

        $data = $response->json();

        if ($this->output->isVerbose()) {
            $this->line('HTTP '.$response->status());
        }

        if (! $response->successful()) {
            return [
                'success' => false,
                'error'   => $response->body(),
            ];
        }

        if (is_array($data) && array_key_exists('success', $data) && $data['success'] === false) {
            return [
                'success' => false,
                'error'   => json_encode($data, JSON_UNESCAPED_UNICODE),
                'raw'     => $data,
            ];
        }

        $counters = $data['data']['counters'] ?? null;

        return [
            'success'  => true,
            'counters' => is_array($counters) ? $counters : null,
            'raw'      => $data,
        ];
    }

    /**
     * Display import counters.
     */
    protected function displayCounters(array $counters, string $type): void
    {
        $this->newLine();
        $this->line("<comment>{$type} API Import Results:</comment>");

        $items = [
            'Endpoints' => [
                'created' => $counters['endpointCreated'] ?? 0,
                'updated' => $counters['endpointUpdated'] ?? 0,
                'failed'  => $counters['endpointFailed'] ?? 0,
                'ignored' => $counters['endpointIgnored'] ?? 0,
            ],
            'Schemas' => [
                'created' => $counters['schemaCreated'] ?? 0,
                'updated' => $counters['schemaUpdated'] ?? 0,
                'failed'  => $counters['schemaFailed'] ?? 0,
                'ignored' => $counters['schemaIgnored'] ?? 0,
            ],
        ];

        foreach ($items as $category => $stats) {
            $this->line("  {$category}: ".
                "created=<info>{$stats['created']}</info>, ".
                "updated=<info>{$stats['updated']}</info>, ".
                "failed=<comment>{$stats['failed']}</comment>, ".
                "ignored={$stats['ignored']}");
        }

        $this->newLine();
    }

    protected function warnIfNoEndpointsImported(array $counters, string $type): void
    {
        $created = (int) ($counters['endpointCreated'] ?? 0);
        $updated = (int) ($counters['endpointUpdated'] ?? 0);
        if ($created + $updated > 0) {
            return;
        }

        $this->warn("{$type}: Apifox reported 0 endpoints created/updated. Check: (1) Project ID is the numeric ID from Project Settings → Open API, (2) correct Apifox region — set APIFOX_API_BASE_URL if needed, (3) run with -v to inspect the API response.");
    }
}
