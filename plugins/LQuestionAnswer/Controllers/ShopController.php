<?php

namespace Plugin\LQuestionAnswer\Controllers;

use Illuminate\Http\Request;
use Plugin\LQuestionAnswer\Repositories\AskAnswerRepo;

class ShopController
{
    public function ask_answer(Request $request)
    {
        $data = AskAnswerRepo::getInstance()->ask_answers($request);

        return read_json_success($data);

    }

    public function store(Request $request)
    {
        try {
            AskAnswerRepo::getInstance()->store($request);

            return create_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }

    public function agree(Request $request)
    {
        try {
            AskAnswerRepo::getInstance()->agree($request);

            return update_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }

    }
}
