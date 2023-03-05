<?php

return [
    'H1' => 'Дилеры',
    'companies' => Model\Company::find(null, ['order' => 'name']),
];
