<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Timeline Template Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the layout views. Feel free to tweak each of these messages here.
    |
    */
    
    'person'            => [
        'create'          => [
            'success'       => ':person_type ":name" successfully added',
            'failed'        => ':person_type ":name" failed to added',
            'timeline'      => 'create new :person_type ":name"',
        ],
        'update'          => [
            'success'       => ':person_type ":name" successfully updated',
            'failed'        => ':person_type ":name" failed to updated',
            'timeline'      => 'update :person_type ":name"',
        ],
        'delete'          => [
            'success'       => ':person_type ":name" successfully deleted',
            'failed'        => ':person_type ":name" failed to deleted',
            'timeline'      => 'delete :person_type ":name"',
        ],
        'bank'          => [
            'failed'       => 'contact error',
        ],
        'contact'          => [
            'failed'       => 'bank error',
        ],
    ],

    'warehouse'            => [
        'create'          => [
            'success'       => 'warehouse ":name" successfully added',
            'failed'        => 'warehouse ":name" failed to added',
            'timeline'      => 'create new warehouse ":name"',
        ],
        'update'          => [
            'success'       => 'warehouse ":name" successfully updated',
            'failed'        => 'warehouse ":name" failed to updated',
            'timeline'      => 'update warehouse ":name"',
        ],
        'delete'          => [
            'success'       => 'warehouse ":name" successfully deleted',
            'failed'        => 'warehouse ":name" failed to deleted',
            'timeline'      => 'delete warehouse ":name"',
        ],
    ],

    'person_group'            => [
        'create'          => [
            'success'       => 'group ":name" successfully added',
            'failed'        => 'group ":name" failed to added',
            'timeline'      => 'create new group ":name"',
        ],
        'update'          => [
            'success'       => 'group ":name" successfully updated',
            'failed'        => 'group ":name" failed to updated',
            'timeline'      => 'update group ":name"',
        ],
        'delete'          => [
            'success'       => 'group ":name" successfully deleted',
            'failed'        => 'group ":name" failed to deleted',
            'timeline'      => 'delete group ":name"',
        ],
    ],

    'item'            => [
        'create'          => [
            'success'       => 'item ":name" successfully added',
            'failed'        => 'item ":name" failed to added',
            'timeline'      => 'create new item ":name"',
        ],
        'update'          => [
            'success'       => 'item ":name" successfully updated',
            'failed'        => 'item ":name" failed to updated',
            'timeline'      => 'update item ":name"',
        ],
        'delete'          => [
            'success'       => 'item ":name" successfully deleted',
            'failed'        => 'item ":name" failed to deleted',
            'timeline'      => 'delete item ":name"',
        ],
        'info'          => [
            'non_stock'     => 'check this if this item type is non stock',
            'minimum_stock' => 'minimum amount of stock',
        ],
    ],

    'item_category'            => [
        'create'          => [
            'success'       => 'item category ":name" successfully added',
            'failed'        => 'item category ":name" failed to added',
            'timeline'      => 'create new item category ":name"',
        ],
        'update'          => [
            'success'       => 'item category ":name" successfully updated',
            'failed'        => 'item category ":name" failed to updated',
            'timeline'      => 'update item category ":name"',
        ],
        'delete'          => [
            'success'       => 'item category ":name" successfully deleted',
            'failed'        => 'item category ":name" failed to deleted',
            'timeline'      => 'delete item category ":name"',
        ],
    ],

    'allocation'            => [
        'create'          => [
            'success'       => 'allocation ":name" successfully added',
            'failed'        => 'allocation ":name" failed to added',
            'timeline'      => 'create new allocation ":name"',
        ],
        'update'          => [
            'success'       => 'allocation ":name" successfully updated',
            'failed'        => 'allocation ":name" failed to updated',
            'timeline'      => 'update allocation ":name"',
        ],
        'delete'          => [
            'success'       => 'allocation ":name" successfully deleted',
            'failed'        => 'allocation ":name" failed to deleted',
            'timeline'      => 'delete allocation ":name"',
        ],
    ],

    'coa'            => [
        'create'          => [
            'success'       => 'coa ":name" successfully added',
            'failed'          => [
                'number'       => 'number already used',
                'save'        => 'coa ":name" failed to added'
            ],
            'timeline'      => 'create new coa ":name"',
        ],
        'update'          => [
            'success'       => 'coa ":name" successfully updated',
            'failed'          => [
                'number'       => 'number already used',
                'save'        => 'coa ":name" failed to added'
            ],
            'timeline'      => 'update coa ":name"',
        ],
        'delete'          => [
            'success'       => 'coa ":name" successfully deleted',
            'failed'        => 'coa ":name" failed to deleted',
            'timeline'      => 'delete coa ":name"',
        ],
    ],

];
