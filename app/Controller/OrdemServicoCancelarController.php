<?php

namespace App\Controller;
use Kernel\Request;
use Kernel\Redirect;

class OrdemServicoCancelarController extends Controller
{
    public function index() {
        return $this->view('viewfolder/index');
    }

    public function create() {
        return $this->view('viewfolder/create');
    }

    public function store(Request $request) {
        
        return Redirect::flashBack([
            'success' => true,
            'message' => 'success'
        ]);
    }

    public function edit($id) {
        return $this->view('viewfolder/update');
    }

    public function update(Request $request) {
        
        return Redirect::flashBack([
            'success' => true,
            'message' => 'success'
        ]);
    }
}