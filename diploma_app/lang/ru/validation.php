<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Строки языка для валидации
    |--------------------------------------------------------------------------
    |
    | Следующие строки содержат стандартные сообщения об ошибках, используемые
    | классом валидатора. Некоторые из этих правил имеют несколько версий,
    | например, правила для размеров. Не стесняйтесь изменять эти сообщения
    |
    */

    'accepted' => 'Поле ":attribute" должно быть принято',
    'accepted_if' => 'Поле ":attribute" должно быть принято, если ":other" равно ":value"',
    'active_url' => 'Поле ":attribute" должно быть действительным URL',
    'after' => 'Поле ":attribute" должно быть датой после ":date"',
    'after_or_equal' => 'Поле ":attribute" должно быть датой после или равной ":date"',
    'alpha' => 'Поле ":attribute" может содержать только буквы',
    'alpha_dash' => 'Поле ":attribute" может содержать только буквы, цифры, дефисы и подчеркивания',
    'alpha_num' => 'Поле ":attribute" может содержать только буквы и цифры',
    'array' => 'Поле ":attribute" должно быть массивом',
    'ascii' => 'Поле ":attribute" должно содержать только однобайтовые буквенно-цифровые символы и символы',
    'before' => 'Поле ":attribute" должно быть датой до ":date"',
    'before_or_equal' => 'Поле ":attribute" должно быть датой до или равной ":date"',
    'between' => [
        'array' => 'Поле ":attribute" должно содержать от ":min" до ":max" элементов',
        'file' => 'Поле ":attribute" должно быть от ":min" до ":max" килобайт',
        'numeric' => 'Поле ":attribute" должно быть от ":min" до ":max"',
        'string' => 'Поле ":attribute" должно быть от ":min" до ":max" символов',
    ],
    'boolean' => 'Поле ":attribute" должно быть true или false',
    'can' => 'Поле ":attribute" содержит неавторизованное значение',
    'confirmed' => 'Поле ":attribute" не совпадает с подтверждением',
    'contains' => 'Поле ":attribute" не содержит обязательного значения',
    'current_password' => 'Неверный пароль',
    'date' => 'Поле ":attribute" должно быть действительной датой',
    'date_equals' => 'Поле ":attribute" должно быть датой, равной ":date"',
    'date_format' => 'Поле ":attribute" должно соответствовать формату ":format"',
    'decimal' => 'Поле ":attribute" должно содержать ":decimal" десятичных знаков',
    'declined' => 'Поле ":attribute" должно быть отклонено',
    'declined_if' => 'Поле ":attribute" должно быть отклонено, если ":other" равно ":value"',
    'different' => 'Поле ":attribute" и ":other" должны различаться',
    'digits' => 'Поле ":attribute" должно содержать ":digits" цифр',
    'digits_between' => 'Поле ":attribute" должно содержать от ":min" до ":max" цифр',
    'dimensions' => 'Поле ":attribute" имеет недопустимые размеры изображения',
    'distinct' => 'Поле ":attribute" содержит повторяющееся значение',
    'doesnt_end_with' => 'Поле ":attribute" не должно заканчиваться одним из следующих: ":values"',
    'doesnt_start_with' => 'Поле ":attribute" не должно начинаться с одного из следующих: ":values"',
    'email' => 'Поле ":attribute" должно быть действительным адресом электронной почты',
    'ends_with' => 'Поле ":attribute" должно заканчиваться одним из следующих: ":values"',
    'enum' => 'Выбранное значение для ":attribute" недопустимо',
    'exists' => 'Выбранное значение для ":attribute" недопустимо',
    'extensions' => 'Поле ":attribute" должно иметь одно из следующих расширений: ":values"',
    'file' => 'Поле ":attribute" должно быть файлом',
    'filled' => 'Поле ":attribute" должно иметь значение',
    'gt' => [
        'array' => 'Поле ":attribute" должно содержать более ":value" элементов',
        'file' => 'Поле ":attribute" должно быть больше ":value" килобайт',
        'numeric' => 'Поле ":attribute" должно быть больше ":value"',
        'string' => 'Поле ":attribute" должно быть больше ":value" символов',
    ],
    'gte' => [
        'array' => 'Поле ":attribute" должно содержать ":value" элементов или более',
        'file' => 'Поле ":attribute" должно быть больше или равно ":value" килобайт',
        'numeric' => 'Поле ":attribute" должно быть больше или равно ":value"',
        'string' => 'Поле ":attribute" должно быть больше или равно ":value" символов',
    ],
    'hex_color' => 'Поле ":attribute" должно быть действительным шестнадцатеричным цветом',
    'image' => 'Поле ":attribute" должно быть изображением',
    'in' => 'Выбранное значение для ":attribute" недопустимо',
    'in_array' => 'Поле ":attribute" должно существовать в ":other"',
    'integer' => 'Поле ":attribute" должно быть целым числом',
    'ip' => 'Поле ":attribute" должно быть действительным IP-адресом',
    'ipv4' => 'Поле ":attribute" должно быть действительным IPv4-адресом',
    'ipv6' => 'Поле ":attribute" должно быть действительным IPv6-адресом',
    'json' => 'Поле ":attribute" должно быть действительной JSON-строкой',
    'list' => 'Поле ":attribute" должно быть списком',
    'lowercase' => 'Поле ":attribute" должно быть в нижнем регистре',
    'lt' => [
        'array' => 'Поле ":attribute" должно содержать менее ":value" элементов',
        'file' => 'Поле ":attribute" должно быть меньше ":value" килобайт',
        'numeric' => 'Поле ":attribute" должно быть меньше ":value"',
        'string' => 'Поле ":attribute" должно быть меньше ":value" символов',
    ],
    'lte' => [
        'array' => 'Поле ":attribute" должно содержать не более ":value" элементов',
        'file' => 'Поле ":attribute" должно быть меньше или равно ":value" килобайт',
        'numeric' => 'Поле ":attribute" должно быть меньше или равно ":value"',
        'string' => 'Поле ":attribute" должно быть меньше или равно ":value" символов',
    ],
    'mac_address' => 'Поле ":attribute" должно быть действительным MAC-адресом',
    'max' => [
        'array' => 'Поле ":attribute" должно содержать не более ":max" элементов',
        'file' => 'Поле ":attribute" не должно превышать ":max" килобайт',
        'numeric' => 'Поле ":attribute" не должно превышать ":max"',
        'string' => 'Поле ":attribute" не должно превышать ":max" символов',
    ],
    'max_digits' => 'Поле ":attribute" не должно содержать более ":max" цифр',
    'mimes' => 'Поле ":attribute" должно быть файлом типа: ":values"',
    'mimetypes' => 'Поле ":attribute" должно быть файлом типа: ":values"',
    'min' => [
        'array' => 'Поле ":attribute" должно содержать не менее ":min" элементов',
        'file' => 'Поле ":attribute" должно быть не менее ":min" килобайт',
        'numeric' => 'Поле ":attribute" должно быть не менее ":min"',
        'string' => 'Поле ":attribute" должно быть не менее ":min" символов',
    ],
    'min_digits' => 'Поле ":attribute" должно содержать не менее ":min" цифр',
    'missing' => 'Поле ":attribute" должно отсутствовать',
    'missing_if' => 'Поле ":attribute" должно отсутствовать, если ":other" равно ":value"',
    'missing_unless' => 'Поле ":attribute" должно отсутствовать, если ":other" не равно ":value"',
    'missing_with' => 'Поле ":attribute" должно отсутствовать, если присутствует ":values"',
    'missing_with_all' => 'Поле ":attribute" должно отсутствовать, если присутствуют ":values"',
    'multiple_of' => 'Поле ":attribute" должно быть кратным ":value"',
    'not_in' => 'Выбранное значение для ":attribute" недопустимо',
    'not_regex' => 'Формат поля ":attribute" недопустим',
    'numeric' => 'Поле ":attribute" должно быть числом',
    'password' => [
        'letters' => 'Поле ":attribute" должно содержать хотя бы одну букву',
        'mixed' => 'Поле ":attribute" должно содержать хотя бы одну заглавную и одну строчную букву',
        'numbers' => 'Поле ":attribute" должно содержать хотя бы одну цифру',
        'symbols' => 'Поле ":attribute" должно содержать хотя бы один символ',
        'uncompromised' => 'Указанное значение ":attribute" было скомпрометировано, пожалуйста, выберите другое значение',
    ],
    'present' => 'Поле ":attribute" должно присутствовать',
    'present_if' => 'Поле ":attribute" должно присутствовать, если ":other" равно ":value"',
    'present_unless' => 'Поле ":attribute" должно присутствовать, если ":other" не равно ":value"',
    'present_with' => 'Поле ":attribute" должно присутствовать, если присутствует ":values"',
    'present_with_all' => 'Поле ":attribute" должно присутствовать, если присутствуют ":values"',
    'prohibited' => 'Поле ":attribute" запрещено',
    'prohibited_if' => 'Поле ":attribute" запрещено, если ":other" равно ":value"',
    'prohibited_if_accepted' => 'Поле ":attribute" запрещено, если ":other" принято',
    'prohibited_if_declined' => 'Поле ":attribute" запрещено, если ":other" отклонено',
    'prohibited_unless' => 'Поле ":attribute" запрещено, если ":other" не равно ":values"',
    'prohibits' => 'Поле ":attribute" запрещает присутствие ":other"',
    'regex' => 'Формат поля ":attribute" недопустим',
    'required' => 'Поле ":attribute" обязательно для заполнения',
    'required_array_keys' => 'Поле ":attribute" должно содержать записи для: ":values"',
    'required_if' => 'Поле ":attribute" обязательно, если ":other" равно ":value"',
    'required_if_accepted' => 'Поле ":attribute" обязательно, если ":other" принято',
    'required_if_declined' => 'Поле ":attribute" обязательно, если ":other" отклонено',
    'required_unless' => 'Поле ":attribute" обязательно, если ":other" не равно ":values"',
    'required_with' => 'Поле ":attribute" обязательно, если присутствует ":values"',
    'required_with_all' => 'Поле ":attribute" обязательно, если присутствуют ":values"',
    'required_without' => 'Поле ":attribute" обязательно, если ":values" отсутствует',
    'required_without_all' => 'Поле ":attribute" обязательно, если ни одно из ":values" не присутствует',
    'same' => 'Поле ":attribute" должно совпадать с ":other"',
    'size' => [
        'array' => 'Поле ":attribute" должно содержать ":size" элементов',
        'file' => 'Поле ":attribute" должно быть ":size" килобайт',
        'numeric' => 'Поле ":attribute" должно быть ":size"',
        'string' => 'Поле ":attribute" должно быть ":size" символов',
    ],
    'starts_with' => 'Поле ":attribute" должно начинаться с одного из следующих: ":values"',
    'string' => 'Поле ":attribute" должно быть строкой',
    'timezone' => 'Поле ":attribute" должно быть действительным часовым поясом',
    'unique' => 'Значение ":attribute" уже занято',
    'uploaded' => 'Не удалось загрузить ":attribute"',
    'uppercase' => 'Поле ":attribute" должно быть в верхнем регистре',
    'url' => 'Поле ":attribute" должно быть действительным URL',
    'ulid' => 'Поле ":attribute" должно быть действительным ULID',
    'uuid' => 'Поле ":attribute" должно быть действительным UUID',

    /*
    |--------------------------------------------------------------------------
    | Пользовательские строки для валидации
    |--------------------------------------------------------------------------
    |
    | Здесь вы можете указать пользовательские сообщения для атрибутов, используя
    | соглашение "attribute.rule" для именования строк. Это позволяет быстро
    | указать конкретное пользовательское сообщение для определенного правила
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'пользовательское-сообщение',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Пользовательские атрибуты
    |--------------------------------------------------------------------------
    |
    | Следующие строки используются для замены заполнителя атрибута на что-то
    | более удобное для чтения, например, "Адрес электронной почты" вместо
    | "email". Это помогает сделать наши сообщения более выразительными
    |
    */

    'attributes' => [],

];
