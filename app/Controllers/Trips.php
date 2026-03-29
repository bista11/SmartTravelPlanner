<?php

namespace App\Controllers;

use App\Models\TripModel;

class Trips extends BaseController
{
    protected $tripModel;

    public function __construct()
    {
        $this->tripModel = new TripModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $search = trim($this->request->getGet('search') ?? '');

        if ($search !== '') {
            $trips = $this->tripModel
                ->groupStart()
                    ->like('destination', $search)
                    ->orLike('country', $search)
                    ->orLike('notes', $search)
                ->groupEnd()
                ->orderBy('id', 'ASC')
                ->findAll();
        } else {
            $trips = $this->tripModel->orderBy('id', 'ASC')->findAll();
        }

        return view('trips/index', [
            'title'  => 'Trips',
            'trips'  => $trips,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('trips/create', [
            'title' => 'Add Trip',
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        $data = [
            'destination' => trim($this->request->getPost('destination')),
            'country'     => trim($this->request->getPost('country')),
            'travel_date' => $this->request->getPost('travel_date'),
            'budget'      => $this->request->getPost('budget'),
            'notes'       => trim($this->request->getPost('notes')),
            'latitude'    => $this->request->getPost('latitude'),
            'longitude'   => $this->request->getPost('longitude'),
        ];

        if (!$this->tripModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->tripModel->errors());
        }

        return redirect()->to('/trips')->with('success', 'Trip added successfully.');
    }

    public function edit($id)
    {
        $trip = $this->tripModel->find($id);

        if (!$trip) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Trip not found.');
        }

        return view('trips/edit', [
            'title'      => 'Edit Trip',
            'trip'       => $trip,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function update($id)
    {
        $trip = $this->tripModel->find($id);

        if (!$trip) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Trip not found.');
        }

        $data = [
            'destination' => trim($this->request->getPost('destination')),
            'country'     => trim($this->request->getPost('country')),
            'travel_date' => $this->request->getPost('travel_date'),
            'budget'      => $this->request->getPost('budget'),
            'notes'       => trim($this->request->getPost('notes')),
            'latitude'    => $this->request->getPost('latitude'),
            'longitude'   => $this->request->getPost('longitude'),
        ];

        if (!$this->tripModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->tripModel->errors());
        }

        return redirect()->to('/trips')->with('success', 'Trip updated successfully.');
    }

    public function delete($id)
    {
        $trip = $this->tripModel->find($id);

        if (!$trip) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Trip not found.');
        }

        $this->tripModel->delete($id);

        return redirect()->to('/trips')->with('success', 'Trip deleted successfully.');
    }
}