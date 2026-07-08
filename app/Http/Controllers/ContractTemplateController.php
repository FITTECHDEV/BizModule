<?php

namespace App\Http\Controllers;

class ContractTemplateController extends Controller
{
    public function index()
    {
        return view('quotation.contract-templates.index');
    }
}