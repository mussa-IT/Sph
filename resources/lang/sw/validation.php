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

    'accepted' => ':attribute lazima ikubaliwe.',
    'accepted_if' => ':attribute lazima ikubaliwe wakati :other ni :value.',
    'active_url' => ':attribute si URL sahihi.',
    'after' => ':attribute lazima iwe tarehe baada ya :date.',
    'after_or_equal' => ':attribute lazima iwe tarehe baada au sawa na :date.',
    'alpha' => ':attribute lazima iwe na herufi pekee.',
    'alpha_dash' => ':attribute lazima iwe na herufi, nambari, dashes na underscores pekee.',
    'alpha_num' => ':attribute lazima iwe na herufi na nambari pekee.',
    'array' => ':attribute lazima iwe safu.',
    'ascii' => ':attribute lazima iwe na herufi za alphanumeric za byte moja na alama pekee.',
    'before' => ':attribute lazima iwe tarehe kabla ya :date.',
    'before_or_equal' => ':attribute lazima iwe tarehe kabla au sawa na :date.',
    'between' => [
        'array' => ':attribute lazima iwe na kati ya :min na :max vitu.',
        'file' => ':attribute lazima iwe kati ya :min na :max kilobytes.',
        'numeric' => ':attribute lazima iwe kati ya :min na :max.',
        'string' => ':attribute lazima iwe kati ya :min na :max herufi.',
    ],
    'boolean' => 'Sehemu ya :attribute lazima iwe kweli au si kweli.',
    'can' => ':attribute ina thamani isiyoidhinishwa.',
    'confirmed' => 'Uthibitisho wa :attribute haulingani.',
    'current_password' => 'Nenosiri si sahihi.',
    'date' => ':attribute si tarehe sahihi.',
    'date_equals' => ':attribute lazima iwe tarehe sawa na :date.',
    'date_format' => ':attribute haulingani na muundo :format.',
    'decimal' => ':attribute lazima iwe na :decimal sehemu za decimal.',
    'declined' => ':attribute lazima ikataliwe.',
    'declined_if' => ':attribute lazima ikataliwe wakati :other ni :value.',
    'different' => ':attribute na :other lazima ziwe tofauti.',
    'digits' => ':attribute lazima iwe :digits tarakimu.',
    'digits_between' => ':attribute lazima iwe kati ya :min na :max tarakimu.',
    'dimensions' => ':attribute ina vipimo batili vya picha.',
    'distinct' => 'Sehemu ya :attribute ina thamani rudufu.',
    'doesnt_end_with' => ':attribute haiwezi kuishia na mojawapo ya yafuatayo: :values.',
    'doesnt_start_with' => ':attribute haiwezi kuanza na mojawapo ya yafuatayo: :values.',
    'email' => ':attribute lazima iwe anwani ya barua pepe sahihi.',
    'ends_with' => ':attribute lazima iishie na mojawapo ya yafuatayo: :values.',
    'enum' => ':attribute iliyochaguliwa si sahihi.',
    'exists' => ':attribute iliyochaguliwa si sahihi.',
    'file' => ':attribute lazima iwe faili.',
    'filled' => 'Sehemu ya :attribute lazima iwe na thamani.',
    'gt' => [
        'array' => ':attribute lazima iwe na zaidi ya :value vitu.',
        'file' => ':attribute lazima iwe kubwa kuliko :value kilobytes.',
        'numeric' => ':attribute lazima iwe kubwa kuliko :value.',
        'string' => ':attribute lazima iwe kubwa kuliko :value herufi.',
    ],
    'gte' => [
        'array' => ':attribute lazima iwe na :value vitu au zaidi.',
        'file' => ':attribute lazima iwe kubwa au sawa na :value kilobytes.',
        'numeric' => ':attribute lazima iwe kubwa au sawa na :value.',
        'string' => ':attribute lazima iwe kubwa au sawa na :value herufi.',
    ],
    'image' => ':attribute lazima iwe picha.',
    'in' => ':attribute iliyochaguliwa si sahihi.',
    'in_array' => 'Sehemu ya :attribute haipo katika :other.',
    'integer' => ':attribute lazima iwe nambari kamili.',
    'ip' => ':attribute lazima iwe anwani ya IP sahihi.',
    'ipv4' => ':attribute lazima iwe anwani ya IPv4 sahihi.',
    'ipv6' => ':attribute lazima iwe anwani ya IPv6 sahihi.',
    'json' => ':attribute lazima iwe kamba ya JSON sahihi.',
    'lowercase' => ':attribute lazima iwe herufi ndogo.',
    'lt' => [
        'array' => ':attribute lazima iwe na chini ya :value vitu.',
        'file' => ':attribute lazima iwe chini ya :value kilobytes.',
        'numeric' => ':attribute lazima iwe chini ya :value.',
        'string' => ':attribute lazima iwe chini ya :value herufi.',
    ],
    'lte' => [
        'array' => ':attribute lazima isiwe na zaidi ya :value vitu.',
        'file' => ':attribute lazima iwe chini au sawa na :value kilobytes.',
        'numeric' => ':attribute lazima iwe chini au sawa na :value.',
        'string' => ':attribute lazima iwe chini au sawa na :value herufi.',
    ],
    'mac_address' => ':attribute lazima iwe anwani ya MAC sahihi.',
    'max' => [
        'array' => ':attribute lazima isiwe na zaidi ya :max vitu.',
        'file' => ':attribute lazima isiwe kubwa kuliko :max kilobytes.',
        'numeric' => ':attribute lazima isiwe kubwa kuliko :max.',
        'string' => ':attribute lazima isiwe kubwa kuliko :max herufi.',
    ],
    'max_digits' => ':attribute lazima isiwe na zaidi ya :max tarakimu.',
    'mimes' => ':attribute lazima iwe faili ya aina: :values.',
    'mimetypes' => ':attribute lazima iwe faili ya aina: :values.',
    'min' => [
        'array' => ':attribute lazima iwe na angalau :min vitu.',
        'file' => ':attribute lazima iwe angalau :min kilobytes.',
        'numeric' => ':attribute lazima iwe angalau :min.',
        'string' => ':attribute lazima iwe angalau :min herufi.',
    ],
    'min_digits' => ':attribute lazima iwe na angalau :min tarakimu.',
    'missing' => 'Sehemu ya :attribute lazima ikosekane.',
    'missing_if' => 'Sehemu ya :attribute lazima ikosekane wakati :other ni :value.',
    'missing_unless' => 'Sehemu ya :attribute lazima ikosekane isipokuwa :other ni :value.',
    'missing_with' => 'Sehemu ya :attribute lazima ikosekane wakati :values iko.',
    'missing_with_all' => 'Sehemu ya :attribute lazima ikosekane wakati :values ziko.',
    'multiple_of' => ':attribute lazima iwe marudio ya :value.',
    'not_in' => ':attribute iliyochaguliwa si sahihi.',
    'not_regex' => 'Muundo wa :attribute si sahihi.',
    'numeric' => ':attribute lazima iwe nambari.',
    'password' => [
        'letters' => ':attribute lazima iwe na angalau herufi moja.',
        'mixed' => ':attribute lazima iwe na angalau herufi kubwa moja na ndogo moja.',
        'numbers' => ':attribute lazima iwe na angalau nambari moja.',
        'symbols' => ':attribute lazima iwe na angalau alama moja.',
        'uncompromised' => ':attribute iliyotolewa imeonekana katika uvujaji wa data. Tafadhali chagua :attribute tofauti.',
    ],
    'present' => 'Sehemu ya :attribute lazima iwepo.',
    'prohibited' => 'Sehemu ya :attribute imepigwa marufuku.',
    'prohibited_if' => 'Sehemu ya :attribute imepigwa marufuku wakati :other ni :value.',
    'prohibited_unless' => 'Sehemu ya :attribute imepigwa marufuku isipokuwa :other iko katika :values.',
    'prohibits' => 'Sehemu ya :attribute inazuia :other kuwa iko.',
    'regex' => 'Muundo wa :attribute si sahihi.',
    'required' => 'Sehemu ya :attribute inahitajika.',
    'required_array_keys' => 'Sehemu ya :attribute lazima iwe na maingizo ya: :values.',
    'required_if' => 'Sehemu ya :attribute inahitajika wakati :other ni :value.',
    'required_if_accepted' => 'Sehemu ya :attribute inahitajika wakati :other imekubaliwa.',
    'required_unless' => 'Sehemu ya :attribute inahitajika isipokuwa :other iko katika :values.',
    'required_with' => 'Sehemu ya :attribute inahitajika wakati :values iko.',
    'required_with_all' => 'Sehemu ya :attribute inahitajika wakati :values ziko.',
    'required_without' => 'Sehemu ya :attribute inahitajika wakati :values haiko.',
    'required_without_all' => 'Sehemu ya :attribute inahitajika wakati hakuna ya :values iko.',
    'same' => ':attribute na :other lazima zilingane.',
    'size' => [
        'array' => ':attribute lazima iwe na :size vitu.',
        'file' => ':attribute lazima iwe :size kilobytes.',
        'numeric' => ':attribute lazima iwe :size.',
        'string' => ':attribute lazima iwe :size herufi.',
    ],
    'starts_with' => ':attribute lazima ianzie na mojawapo ya yafuatayo: :values.',
    'string' => ':attribute lazima iwe kamba.',
    'timezone' => ':attribute lazima iwe eneo la muda sahihi.',
    'unique' => ':attribute tayari imechukuliwa.',
    'uploaded' => ':attribute imeshindwa kupakiwa.',
    'uppercase' => ':attribute lazima iwe herufi kubwa.',
    'url' => ':attribute lazima iwe URL sahihi.',
    'ulid' => ':attribute lazima iwe ULID sahihi.',
    'uuid' => ':attribute lazima iwe UUID sahihi.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute". This makes it quick to specify a specific
    | custom language line for a given attribute rule.
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

    'attributes' => [],

];