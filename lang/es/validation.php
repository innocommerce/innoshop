<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'        => 'El campo :attribute debe ser aceptado.',
    'active_url'      => 'El campo :attribute no es una URL válida.',
    'after'           => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal'  => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha'           => 'El campo :attribute solo puede contener letras.',
    'alpha_dash'      => 'El campo :attribute solo puede contener letras, números y guiones.',
    'alpha_num'       => 'El campo :attribute solo puede contener letras y números.',
    'array'           => 'El campo :attribute debe ser un array.',
    'before'          => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between'         => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file'    => 'El campo :attribute debe tener un tamaño entre :min y :max kilobytes.',
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'boolean'        => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'      => 'La confirmación del campo :attribute no coincide.',
    'date'           => 'El campo :attribute no es una fecha válida.',
    'date_format'    => 'El campo :attribute no coincide con el formato de fecha :format.',
    'different'      => 'El campo :attribute y :other deben ser diferentes.',
    'digits'         => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'dimensions'     => 'Las dimensiones de la imagen :attribute no son válidas.',
    'distinct'       => 'El campo :attribute debe tener valores distintos.',
    'email'          => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
    'ends_with'      => 'El campo :attribute debe terminar con uno de los siguientes valores: :values.',
    'exists'         => 'El campo :attribute seleccionado no existe.',
    'file'           => 'El campo :attribute debe ser un archivo.',
    'filled'         => 'El campo :attribute es obligatorio.',
    'gt'             => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'file'    => 'El campo :attribute debe tener un tamaño mayor que :value kilobytes.',
        'string'  => 'El campo :attribute debe tener más de :value caracteres.',
        'array'   => 'El campo :attribute debe tener más de :value elementos.',
    ],
    'gte' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'file'    => 'El campo :attribute debe tener un tamaño mayor o igual que :value kilobytes.',
        'string'  => 'El campo :attribute debe tener al menos :value caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :value elementos.',
    ],
    'image'    => 'El campo :attribute debe ser una imagen.',
    'in'       => 'El campo :attribute seleccionado no es válido.',
    'in_array' => 'El campo :attribute no existe en :values.',
    'integer'  => 'El campo :attribute debe ser un número entero.',
    'ip'       => 'El campo :attribute debe ser una dirección IP válida.',
    'ipv4'     => 'El campo :attribute debe ser una dirección IPv4 válida.',
    'ipv6'     => 'El campo :attribute debe ser una dirección IPv6 válida.',
    'json'     => 'El campo :attribute debe ser una cadena JSON válida.',
    'lt'       => [
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'file'    => 'El campo :attribute debe tener un tamaño menor que :value kilobytes.',
        'string'  => 'El campo :attribute debe tener menos de :value caracteres.',
        'array'   => 'El campo :attribute debe tener menos de :value elementos.',
    ],
    'lte' => [
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
        'file'    => 'El campo :attribute debe tener un tamaño menor o igual que :value kilobytes.',
        'string'  => 'El campo :attribute debe tener como máximo :value caracteres.',
        'array'   => 'El campo :attribute debe tener como máximo :value elementos.',
    ],
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file'    => 'El campo :attribute no debe tener un tamaño mayor que :max kilobytes.',
        'string'  => 'El campo :attribute no debe tener más de :max caracteres.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'mimes'     => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min'       => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file'    => 'El campo :attribute debe tener un tamaño mínimo de :min kilobytes.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'not_in'               => 'El campo :attribute seleccionado no es válido.',
    'not_regex'            => 'El campo :attribute no coincide con el formato requerido.',
    'numeric'              => 'El campo :attribute debe ser un número.',
    'present'              => 'El campo :attribute debe estar presente.',
    'regex'                => 'El formato del campo :attribute no es válido.',
    'required'             => 'El campo :attribute es obligatorio.',
    'required_if'          => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless'      => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all'    => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without'     => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values está presente.',
    'same'                 => 'El campo :attribute y :other deben ser iguales.',
    'size'                 => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file'    => 'El campo :attribute debe tener un tamaño de :size kilobytes.',
        'string'  => 'El campo :attribute debe tener :size caracteres.',
        'array'   => 'El campo :attribute debe tener :size elementos.',
    ],
    'starts_with' => 'El campo :attribute debe comenzar con uno de los siguientes valores: :values.',
    'string'      => 'El campo :attribute debe ser una cadena de caracteres.',
    'timezone'    => 'El campo :attribute debe ser una zona horaria válida.',
    'unique'      => 'El campo :attribute ya ha sido registrado.',
    'uploaded'    => 'El campo :attribute no se pudo subir.',
    'url'         => 'El campo :attribute debe ser una URL válida.',
    'uuid'        => 'El campo :attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
];
