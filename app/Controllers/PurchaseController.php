<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class PurchaseController extends BaseController
{
    public function store()
    {
        $rules = [
            'purchase_date' => 'required|valid_date',
            'product_id.*' => 'required|is_not_unique[products.id]',
            'cost.*' => 'required|decimal',
            'quantity.*' => 'required|integer|greater_than[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->formatDataForStorage($this->request->getPost());
        $isPurchaseCreated = $this->createPurchase($data['purchaseData'], $data['purchaseItemsData']);

        if ($isPurchaseCreated === false) {
            return redirect()->back()->with('error', 'Database error: Could not save purchase.');
        }
        return redirect()->back()->with('success', 'Purchase saved successfully');

    }

    public function formatDataForStorage($input)
    {
        $productIds = $input['product_id'];

        $products = $this->getProductByIds($productIds);

        $items = [];
        $grandTotal = 0;

        foreach ($productIds as $index => $productId) {

            $product = $products[$productId];
            $basePrice = (float) $product['sale_price'];
            $quantity = (int) $input['quantity'][$index];

            $finalUnitPrice = $basePrice;
            if ((int) $product['is_vat_applicable'] === 1) {
                $vatAmount = ($basePrice * (float) $product['vat_percent']) / 100;
                $finalUnitPrice = $basePrice + $vatAmount;
            }

            $subtotal = $finalUnitPrice * $quantity;
            $grandTotal += $subtotal;

            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $finalUnitPrice,
                'subtotal' => $subtotal
            ];
        }

        return [
            'purchaseData' => [
                'supplier_id' => $input['supplier'],
                'purchase_date' => $input['purchase_date'],
                'total_amount' => $grandTotal,
                'status' => 'draft'
            ],
            'purchaseItemsData' => $items
        ];
    }

    public function getProductByIds($productIds)
    {
        $db = Database::connect();
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $sql = "SELECT id, sale_price, is_vat_applicable, vat_percent 
            FROM products 
            WHERE id IN ($placeholders)";

        $query = $db->query($sql, $productIds);
        $results = $query->getResultArray();

        $indexed = [];
        foreach ($results as $row) {
            $indexed[$row['id']] = $row;
        }
        return $indexed;

    }

    public function createPurchase($purchaseData, $purchaseItemsData)
    {
        $db = Database::connect();

        $db->transStart();
        $sqlHeader = "INSERT INTO purchases (supplier_id, purchase_date, total_amount, status, created_at) VALUES (?, ?, ?, 'draft',?)";
        $db->query($sqlHeader, [
            $purchaseData['supplier_id'],
            $purchaseData['purchase_date'],
            $purchaseData['total_amount'],
            date('Y-m-d H:i:s')
        ]);

        $purchaseId = $db->insertID();
        $sqlItem = "INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, subtotal, created_at) VALUES (?, ?, ?, ?, ?,?)";
        foreach ($purchaseItemsData as $item) {
            $db->query($sqlItem, [
                $purchaseId,
                $item['product_id'],
                $item['quantity'],
                $item['unit_price'],
                $item['subtotal'],
                date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();
        return $db->transStatus();

    }



}