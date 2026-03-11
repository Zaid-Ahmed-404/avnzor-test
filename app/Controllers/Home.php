<?php

namespace App\Controllers;

use Config\Database;

class Home extends BaseController
{
    public function index(): string
    {

        $db = Database::connect();
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $query = $db->query($sql);

        $data['products'] = $query->getResult();

        $data['suppliers'] = [
            ['id' => 1, 'name' => 'Test Supplier 1'],
            ['id' => 2, 'name' => 'Test Supplier 2'],
            ['id' => 3, 'name' => 'Test Supplier 3']
        ];
        return view('purchase/create', $data);

    }


}
