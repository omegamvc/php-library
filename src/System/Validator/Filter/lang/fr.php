<?php

return [
    'required'             => 'Le champ {field} est obligatoire',
    'valid_email'          => 'Le champ {field} doit &#234;tre un email valide',
    'max_len'              => 'Le champ {field} doit avoir un nombre de caract&#232;re de {param} ou moins',
    'min_len'              => 'Le champ {field} doit avoir un nombre de caract&#232;re de {param} ou plus',
    'between_len'             => 'Le champ {field} doit avoir un nombre de caractères entre {param} et {param2}',
    // phpcs:ignore
    'alpha_numeric_dash'      => 'Le champ {field} doit seulement contenir des caractères alpha (a-z), numériques (0-9) et tirets',
    // phpcs:ignore
    'alpha_numeric_space'     => 'Le champ {field} doit seulement contenir des caractères alpha (a-z), numériques (0-9) et espaces',
    'exact_len'            => 'Le champ {field} doit avoir un nombre de caract&#232;re de {param}',
    'alpha'                => 'Le champ {field} doit seulement contenir des caract&#232;res alpha (a-z)',
    'alpha_numeric'        => 'Le champ {field} doit seulement contenir des caract&#232;res alpha-num&#233;rique (a-z)',
    'alpha_dash'           => 'Le champ {field} doit seulement contenir des caract&#232;res alpha (a-z) et tiret',
    'alpha_space'          => 'Le champ {field} doit seulement contenir des caract&#232;res alpha (a-z) et espace',
    'numeric'              => 'Le champ {field} doit seulement contenir des caract&#232;res num&#233;riques',
    'integer'              => 'Le champ {field} doit &#234;tre une valeur num&#233;rique',
    'boolean'              => 'Le champ {field} doit &#234;tre vrai ou faux',
    'float'                => 'Le champ {field} doit &#234;tre une valeur d&#233;cimale',
    'valid_url'            => 'Le champ {field} doit &#234;tre une URL valide',
    'url_exists'           => 'L&#39;URL {field} n&#39;existe pas',
    'valid_ip'             => 'Le champ {field} doit contenir une adresse IP valide',
    'valid_ipv4'           => 'Le champ {field} doit contenir une adresse IPv4 valide',
    'valid_ipv6'           => 'Le champ {field} doit contenir une adresse IPv6 valide',
    'guidv4'               => 'Le champ {field} doit contenir un GUID valide',
    'valid_cc'             => 'Le champ {field} doit contenir un num&#233;ro de carte bancaire valide',
    'valid_name'           => 'Le champ {field} doit contenir un nom humain valide',
    'contains'             => 'Le champ {field} doit contenir une des ces valeurs: {param}',
    'contains_list'        => 'Le champ {field} doit contenir une valeur du menu d&#233;roulant',
    'doesnt_contain_list'  => 'Le champ {field} contient une valeur qui n&#39;est pas acceptable',
    'street_address'       => 'Le champ {field} doit &#234;tre une adresse postale valide',
    'date'                 => 'Le champ {field} doit &#234;tre une date valide',
    // phpcs:ignore
    'min_numeric'          => 'Le champ {field} doit &#234;tre une valeur num&#233;rique &#233;gale ou sup&#233;rieur à {param}',
    // phpcs:ignore
    'max_numeric'          => 'Le champ {field} doit &#234;tre une valeur num&#233;rique &#233;gale ou inf&#233;rieur à {param}',
    'min_age'              => 'Le champ {field} doit &#234;tre un &#226;ge &#233;gal ou sup&#233;rieur à {param}',
    'starts'               => 'Le champ {field} doit commencer par {param}',
    'extension'            => 'Le champ {field} doit avoir les extensions suivantes {param}',
    'required_file'        => 'Le champ {field} est obligatoire',
    'equalsfield'          => 'Le champ {field} n&#39;est pas &#233;gale au champ {param}',
    'iban'                 => 'Le champ {field} doit contenir un IBAN valide',
    'phone_number'         => 'Le champ {field} doit contenir un num&#233;ro de t&#233;l&#233;phone valide',
    'regex'                => 'Le champ {field} doit contenir une valeur valide',
    // phpcs:ignore
    'valid_array_size_greater' => 'Le champ {field} doit être un tableau dont la taille est supérieure ou égale à {param}',
    // phpcs:ignore
    'valid_array_size_lesser' => 'Le champ {field} doit être un tableau dont la taille est inférieure ou égale à {param}',
    'valid_array_size_equal'  => 'Le champ {field} doit être un tableau dont la taille est égale à {param}',
    'valid_json_string'    => 'Le champ {field} doit avoir un format JSON',
];
