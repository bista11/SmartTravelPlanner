<?php

namespace App\Models;

use CodeIgniter\Model;

class TripModel extends Model
{
    protected $table            = 'trips';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'destination',
        'country',
        'travel_date',
        'budget',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'destination' => 'required|min_length[2]|max_length[150]',
        'country'     => 'required|min_length[2]|max_length[100]',
        'travel_date' => 'permit_empty|valid_date',
        'budget'      => 'permit_empty|decimal',
        'notes'       => 'permit_empty',
        'latitude'    => 'permit_empty|decimal',
        'longitude'   => 'permit_empty|decimal',
    ];

    protected $validationMessages = [
        'destination' => [
            'required' => 'Destination is required.',
        ],
        'country' => [
            'required' => 'Country is required.',
        ],
    ];
}