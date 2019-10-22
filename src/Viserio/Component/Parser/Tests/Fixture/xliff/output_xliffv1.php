<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    'version' => 1.2,
    'source-language' => 'en',
    'target-language' => 'x-zz',
    'The set of
                     is {
                    , ...}.
                ' => [
        'source' => 'The set of
                     is {
                    , ...}.
                ',
        'target' => 'Zthe zset zof
                     zis {
                    , ...}.
                ',
        'id' => '135956960462609535',
        'notes' => [
            0 => [
                'content' => 'Example: The set of prime numbers is {2, 3, 5, 7, 11, 13, ...}.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    '
                     took a trip to
                    .
                ' => [
        'source' => '
                     took a trip to
                    .
                ',
        'target' => '
                     ztook za ztrip zto
                    .
                ',
        'id' => '768490705511913603',
        'notes' => [
            0 => [
                'content' => 'Example: Alice took a trip to wonderland.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
        'target-attributes' => [
            'state' => 'new',
        ],
    ],
    '
                     is nowhere near the value of pi.
                ' => [
        'source' => '
                     is nowhere near the value of pi.
                ',
        'target' => '
                     zis znowhere znear zthe zvalue zof zpi.
                ',
        'id' => '889614911019327165',
        'notes' => [
            0 => [
                'content' => 'Example: 5 is nowhere near the value of pi.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    'Your favorite keyword' => [
        'source' => 'Your favorite keyword',
        'target' => 'Zyour zfavorite zkeyword',
        'id' => '2209690285855487595',
        'notes' => [
            0 => [
                'content' => 'Ask user to pick best keyword',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    'Hello world!' => [
        'source' => 'Hello world!',
        'target' => 'Zhello zworld!',
        'id' => '3022994926184248873',
        'notes' => [
            0 => [
                'content' => 'Says hello to the world.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    '
                     took a trip.
                ' => [
        'source' => '
                     took a trip.
                ',
        'target' => '
                     ztook za ztrip.
                ',
        'id' => '3179387603303514412',
        'notes' => [
            0 => [
                'content' => 'Example: Alice took a trip.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    'A trip was taken.' => [
        'source' => 'A trip was taken.',
        'target' => 'Za ztrip zwas ztaken.',
        'id' => '3329840836245051515',
    ],
    'Archive' => [
        'source' => 'Archive',
        'target' => 'Zarchive',
        'id' => '7224011416745566687',
        'notes' => [
            0 => [
                'content' => 'The word \'Archive\' used as a noun, i.e. an information store.',
                'priority' => 1,
                'from' => 'description',
            ],
            1 => [
                'content' => 'noun',
                'priority' => 1,
                'from' => 'meaning',
            ],
        ],
    ],
    'Click
                    here
                     to access Labs.
                ' => [
        'source' => 'Click
                    here
                     to access Labs.
                ',
        'target' => 'Zclick
                    zhere
                     zto zaccess Zlabs.
                ',
        'id' => '5539341884085868292',
        'notes' => [
            0 => [
                'content' => 'Link to the unreleased \'Labs\' feature.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    '
                     is a good approximation of pi.
                ' => [
        'source' => '
                     is a good approximation of pi.
                ',
        'target' => '
                     zis za zgood zapproximation zof zpi.
                ',
        'id' => '6820146346443344314',
        'notes' => [
            0 => [
                'content' => 'Example: 3.1416 is a good approximation of pi.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    '
                     is a bad approximation of pi.
                ' => [
        'source' => '
                     is a bad approximation of pi.
                ',
        'target' => '
                     zis za zbad zapproximation zof zpi.
                ',
        'id' => '6820284805811944992',
        'notes' => [
            0 => [
                'content' => 'Example: 3.1 is a bad approximation of pi.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    'Hello
                    !
                ' => [
        'source' => 'Hello
                    !
                ',
        'target' => 'Zhello
                    !
                ',
        'id' => '6936162475751860807',
        'notes' => [
            0 => [
                'content' => 'Says hello to a person.',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    'by
                     (
                    )
                ' => [
        'source' => 'by
                     (
                    )
                ',
        'target' => 'zby
                     (
                    )
                ',
        'id' => '7036633296476174078',
        'notes' => [
            0 => [
                'content' => 'Indicates who wrote the book and when, e.g. \'by Rudyard Kipling (1892)\'',
                'priority' => 1,
                'from' => 'description',
            ],
        ],
    ],
    'Help' => [
        'source' => 'Help',
        'target' => 'Zhelp',
        'id' => '7911416166208830577',
        'notes' => [
            0 => [
                'content' => 'Link to Help',
                'priority' => 1,
                'from' => 'description',
            ],
            1 => [
                'content' => 'Link to Help2',
                'priority' => 2,
                'from' => 'description',
            ],
        ],
    ],
];
