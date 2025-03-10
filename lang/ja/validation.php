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

    'accepted' => ':attributeを承認する必要があります。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認する必要があります。',
    'active_url' => ':attributeは有効なURLである必要があります。',
    'after' => ':attributeは:date以降の日付である必要があります。',
    'after_or_equal' => ':attributeは:dateと同日またはそれ以降の日付である必要があります。',
    'alpha' => ':attributeは文字のみ含めることができます。',
    'alpha_dash' => ':attributeは文字、数字、ダッシュ、アンダースコアのみ含めることができます。',
    'alpha_num' => ':attributeは文字と数字のみ含めることができます。',
    'array' => ':attributeは配列である必要があります。',
    'ascii' => ':attributeは半角英数字と記号のみ含めることができます。',
    'before' => ':attributeは:date以前の日付である必要があります。',
    'before_or_equal' => ':attributeは:dateと同日またはそれ以前の日付である必要があります。',
    'between' => [
        'array' => ':attributeの項目数は:minから:maxの間である必要があります。',
        'file' => ':attributeのファイルサイズは:minから:maxキロバイトの間である必要があります。',
        'numeric' => ':attributeは:minから:maxの間である必要があります。',
        'string' => ':attributeの文字数は:minから:maxの間である必要があります。',
    ],
    'boolean' => ':attributeはtrueまたはfalseである必要があります。',
    'can' => ':attributeに不正な値が含まれています。',
    'confirmed' => ':attributeの確認が一致しません。',
    'contains' => ':attributeに必須の値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは有効な日付である必要があります。',
    'date_equals' => ':attributeは:dateと同じ日付である必要があります。',
    'date_format' => ':attributeはフォーマット:formatに一致する必要があります。',
    'decimal' => ':attributeは:decimal桁の小数である必要があります。',
    'declined' => ':attributeは辞退する必要があります。',
    'declined_if' => ':otherが:valueの場合、:attributeは辞退する必要があります。',
    'different' => ':attributeと:otherは異なる必要があります。',
    'digits' => ':attributeは:digits桁である必要があります。',
    'digits_between' => ':attributeは:minから:max桁である必要があります。',
    'dimensions' => ':attributeは無効な画像サイズです。',
    'distinct' => ':attributeには重複する値があります。',
    'doesnt_end_with' => ':attributeは次のいずれかで終わってはなりません: :values。',
    'doesnt_start_with' => ':attributeは次のいずれかで始まってはなりません: :values。',
    'email' => ':attributeは有効なメールアドレスである必要があります。',
    'ends_with' => ':attributeは次のいずれかで終わる必要があります: :values。',
    'enum' => '選択された:attributeは無効です。',
    'exists' => '選択された:attributeは無効です。',
    'extensions' => ':attributeの拡張子は次のいずれかである必要があります: :values。',
    'file' => ':attributeはファイルである必要があります。',
    'filled' => ':attributeには値が必要です。',
    'gt' => [
        'array' => ':attributeには:value個以上の項目が必要です。',
        'file' => ':attributeは:valueキロバイトより大きい必要があります。',
        'numeric' => ':attributeは:valueより大きい必要があります。',
        'string' => ':attributeは:value文字より多い必要があります。',
    ],
    'gte' => [
        'array' => ':attributeには:value個以上の項目が必要です。',
        'file' => ':attributeは:valueキロバイト以上である必要があります。',
        'numeric' => ':attributeは:value以上である必要があります。',
        'string' => ':attributeは:value文字以上である必要があります。',
    ],
    'hex_color' => ':attributeは有効な16進数の色である必要があります。',
    'image' => ':attributeは画像である必要があります。',
    'in' => '選択された:attributeは無効です。',
    'in_array' => ':attributeは:otherに存在する必要があります。',
    'integer' => ':attributeは整数である必要があります。',
    'ip' => ':attributeは有効なIPアドレスである必要があります。',
    'ipv4' => ':attributeは有効なIPv4アドレスである必要があります。',
    'ipv6' => ':attributeは有効なIPv6アドレスである必要があります。',
    'json' => ':attributeは有効なJSON文字列である必要があります。',
    'list' => ':attributeはリストである必要があります。',
    'lowercase' => ':attributeは小文字である必要があります。',
    'lt' => [
        'array' => ':attributeには:value個未満の項目が必要です。',
        'file' => ':attributeは:valueキロバイト未満である必要があります。',
        'numeric' => ':attributeは:value未満である必要があります。',
        'string' => ':attributeは:value文字未満である必要があります。',
    ],
    'lte' => [
        'array' => ':attributeには:value個以下の項目が必要です。',
        'file' => ':attributeは:valueキロバイト以下である必要があります。',
        'numeric' => ':attributeは:value以下である必要があります。',
        'string' => ':attributeは:value文字以下である必要があります。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスである必要があります。',
    'max' => [
        'array' => ':attributeには:max個以下の項目が必要です。',
        'file' => ':attributeは:maxキロバイト以下である必要があります。',
        'numeric' => ':attributeは:max以下である必要があります。',
        'string' => ':attributeは:max文字以下である必要があります。',
    ],
    'max_digits' => ':attributeは:max桁以下である必要があります。',
    'mimes' => ':attributeのファイルタイプは:valuesである必要があります。',
    'mimetypes' => ':attributeのファイルタイプは:valuesである必要があります。',
    'min' => [
        'array' => ':attributeには少なくとも:min個の項目が必要です。',
        'file' => ':attributeは少なくとも:minキロバイトである必要があります。',
        'numeric' => ':attributeは少なくとも:minである必要があります。',
        'string' => ':attributeは少なくとも:min文字である必要があります。',
    ],
    'min_digits' => ':attributeは少なくとも:min桁である必要があります。',
    'missing' => ':attributeが存在しない必要があります。',
    'missing_if' => ':otherが:valueの場合、:attributeが存在しない必要があります。',
    'missing_unless' => ':otherが:valueでない限り、:attributeが存在しない必要があります。',
    'missing_with' => ':valuesが存在する場合、:attributeが存在しない必要があります。',
    'missing_with_all' => ':valuesがすべて存在する場合、:attributeが存在しない必要があります。',
    'multiple_of' => ':attributeは:valueの倍数である必要があります。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeは数値である必要があります。',
    'password' => [
        'letters' => ':attributeには少なくとも1文字を含める必要があります。',
        'mixed' => ':attributeには少なくとも1つの大文字と1つの小文字を含める必要があります。',
        'numbers' => ':attributeには少なくとも1つの数字を含める必要があります。',
        'symbols' => ':attributeには少なくとも1つの記号を含める必要があります。',
        'uncompromised' => '指定された:attributeはデータ漏洩に含まれています。別の:attributeを選択してください。',
    ],
    'present' => ':attributeが存在する必要があります。',
    'present_if' => ':otherが:valueの場合、:attributeが存在する必要があります。',
    'present_unless' => ':otherが:valueでない限り、:attributeが存在する必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeが存在する必要があります。',
    'present_with_all' => ':valuesがすべて存在する場合、:attributeが存在する必要があります。',
    'prohibited' => ':attributeは入力禁止です。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは入力禁止です。',
    'prohibited_unless' => ':otherが:valuesでない限り、:attributeは入力禁止です。',
    'prohibits' => ':attributeがある場合、:otherを含めることはできません。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeは必須です。',
    'required_array_keys' => ':attributeには次の項目が含まれている必要があります: :values。',
    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_if_accepted' => ':otherが承認されている場合、:attributeは必須です。',
    'required_if_declined' => ':otherが拒否されている場合、:attributeは必須です。',
    'required_unless' => ':otherが:valuesに含まれない限り、:attributeは必須です。',
    'required_with' => ':valuesが存在する場合、:attributeは必須です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeは必須です。',
    'required_without' => ':valuesが存在しない場合、:attributeは必須です。',
    'required_without_all' => ':valuesが全て存在しない場合、:attributeは必須です。',
    'same' => ':attributeと:otherが一致する必要があります。',
    'size' => [
        'array' => ':attributeには:size個の項目が必要です。',
        'file' => ':attributeのファイルサイズは:sizeキロバイトである必要があります。',
        'numeric' => ':attributeは:sizeである必要があります。',
        'string' => ':attributeは:size文字である必要があります。',
    ],
    'starts_with' => ':attributeは次のいずれかで始まる必要があります: :values。',
    'string' => ':attributeは文字列である必要があります。',
    'timezone' => ':attributeは有効なタイムゾーンである必要があります。',
    'unique' => ':attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字である必要があります。',
    'url' => ':attributeは有効なURLである必要があります。',
    'ulid' => ':attributeは有効なULIDである必要があります。',
    'uuid' => ':attributeは有効なUUIDである必要があります。',


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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        "orientation" => "画面向き"
    ],

];
