<?php

// sebuah class untuk memproses item-item produk di product.txt menjadi sebuah array
class Catalogue
{
    // method untuk mengganti key / index pada array
    function createProductColumn($columns, $listOfRawProduct){
        // perulangan untuk mengambil key/index dari array yang dikirim
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
            // memasukan value dari key/index lama [0], [1] ke key/index baru [item], [price]
            $listOfRawProduct[$columns[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            // menghapus array lama
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
    
        return $listOfRawProduct; // mengembalikan array baru
    }

    // method untuk memanggil file products.txt dan memproses nya ke array
    function product($parameters){
        $collectionOfListProduct = []; // variable untuk menampung array product yang telah diproses

        $raw_data = file($parameters['file_name']); // memanggil file products.txt dan disimpan dengan bentuk array
        
        // perulangan untuk memproses array di $raw_data
        foreach($raw_data as $listOfRawProduct){
            //mengirim dua argumen, berupa value dari array column dan array yang sudah dipisah dengan fungsi explode
            $collectionOfListProduct[] = $this->createProductColumn($parameters['columns'], explode(",", $listOfRawProduct));
        }
        // mengembalikan array berisi array dari daftar produk yang sudah diganti key/index nya
        // dan jumlah array dari daftar produk
        return [
            "product" => $collectionOfListProduct,
            "gen_length" => count($collectionOfListProduct)
        ];
    }
}

// class untuk membentuk populasi
class PopulationGenerator
{
    // method untuk membuat individu / kromosom
    function createIndividu($parameters){
        // membuat objek dari class Catalogue
        $catalogue = new Catalogue;
        // mengambil jumlah dari array daftar produk
        $lengthOfGen = $catalogue->product($parameters)['gen_length'];
        // perulangan untuk mengisi kromosom dengan gen random 0-1
        for ($i = 0; $i <= $lengthOfGen-1; $i++){
            $ret[] = rand(0, 1);
        }
        return $ret;
    } 

    // method untuk membuat populasi
    function createPopulation($parameters){
        // mengisi array ret[] dengan individu
        for($i = 0; $i <= $parameters['population_size']; $i++){
            $ret[] = $this->createIndividu($parameters);
        }
        
        foreach($ret as $val){
            print_r($val);
            echo '<br>';
        }
    }
}

// array berisi informasi-informasi yang dibutuhkan untuk project
$parameters = [
    'file_name' => 'products.txt',
    'columns' => ['item', 'price'],
    'population_size' => 10
];

$katalog = new Catalogue;
$katalog->product($parameters);

$initialPopulation = new PopulationGenerator;
$initialPopulation->createPopulation($parameters);
