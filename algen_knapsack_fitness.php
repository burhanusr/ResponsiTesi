<?php

// class berisi informasi yang dibutuhkan project
class Parameters {
    const FILE_NAME = 'products.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET = 280000;
    const STOPPING_VALUE = 10000;
}

// class untuk memproses item-item produk di product.txt menjadi sebuah array
class Catalogue
{
    // method untuk mengganti key / index pada array
    function createProductColumn($listOfRawProduct){
        // perulangan untuk mengambil key/index dari array yang dikirim
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
             // memasukan value dari key/index lama [0], [1] ke key/index baru [item], [price]
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            // menghapus array lama
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct; // mengembalikan array baru
    }

    // method untuk memanggil file products.txt dan memproses nya ke array
    function product(){
        $collectionOfListProduct = []; // variable untuk menampung array product yang telah diproses

        $raw_data = file(Parameters::FILE_NAME); // memanggil file products.txt dan disimpan dengan bentuk array
        // perulangan untuk memproses array di $raw_data
        foreach($raw_data as $listOfRawProduct){
            //mengirim argumen, berupa  array yang sudah dipisah dengan fungsi explode
            $collectionOfListProduct[] = $this->createProductColumn(explode(",", $listOfRawProduct));
        }
        return $collectionOfListProduct; // mengembalikan array daftar produk
    }
}

// class untuk membentuk individu
class Individu 
{
    // method untuk mengambil jumlah dari array daftar produk
    function countNumberOfGen(){
        $catalogue = new Catalogue;
        return count($catalogue->product());
    }
    // method untuk membuat individu acak
    function createRandomIndividu(){
        for($i = 0; $i <= $this->countNumberOfGen()-1; $i++){
            $ret[] = rand(0,1);
        }
        return $ret;
    }
}

// class untuk membentuk populasi
class Population
{
    // method untuk membuat populasi
    function createRandomPopulation(){
        // membuat objek dari class Individu
        $individu = new Individu;
         // mengisi array ret[] dengan individu
        for($i = 0; $i <= Parameters::POPULATION_SIZE - 1; $i++){
            $ret[] = $individu->createRandomIndividu();
        }

        return $ret;
    }
}

// sebuah class berisikan fungsi fitness untuk mengecek kualitas dari tiap individu
class Fitness
{
    // method untuk mencari gen dengan biner 1
    // argumen berupa array individu
    function selectingItem($individu)
    {
        $cataloge = new Catalogue;
        // perulangan untuk membaca nilai di array individu
        foreach($individu as $individuKey => $binaryGen) {
            // jika gen tersebut bernilai 1, maka index gen dan key 'price' (dari class Catalgoue)
            // akan dimasukkan ke array ret[]
            if($binaryGen === 1) {
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $cataloge->product()[$individuKey]['price']
                ];
            }
        } 

        return $ret;
    }

    // method untuk menghitung jumlah total harga dari gen-gen yang terpilih
    function calculateFitnessValue($individu)
    {
        return array_sum(array_column($this->selectingItem($individu), 'selectedPrice'));
    }

    // method untuk menghitung jumlah total gen yang terpilih
    function countSelectedItem($individu)
    {
        return count($this->selectingItem($individu));
    }

    // method untuk mencari individu berkualitas berdasarkan fungsi fitness
    function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem) 
    {
        // ketika individu memiliki gen dengan nilai terbesar hanya 1
        if ($numberOfIndividuHasMaxItem === 1) {
            // maka akan dilakukan pencarian array nya dengan informasi yaitu nilai terbesar dicocokkan dengan
            // value 'numberOfSelectedItem' dari array $fits, ketika kecocokan ditemukan
            // maka akan memberikan nilai index letak value dengan nilai terbesar tersebut
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            print_r($fits[$index]);
            return $fits[$index];
        } else {
            // ketika individu memiliki gen dengan nilai terbesar lebih dari 1, maka akan dilakukan perulangan untuk mencarinya
            foreach ($fits as $key => $val) {
                // jika value pada array cocok dengan max item terbesar
                if ($val['numberOfSelectedItem'] === $maxItem) {
                    // maka ditampilkan index nya di array $fits dan value price nya
                    echo "Indivdu ke-$key , Fitness Value : " . $val['fitnessValue'] . "<br>";
                    // kemudian index dan value price tadi dimasukkan ke array $ret
                    // untuk selanjutnya dibandingkan mana yang lebih baik
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }

            // MEMBANDINGKAN INDIVIDU YANG LEBIH BAIK
            // jika total harga dari individu2 yang lolos tadi sama, maka akan diambil acak
            if (count(array_unique(array_column($ret, 'fitnessValue'))) === 1) {
                $index = rand(0, count($ret) - 1);
            } else {
                // jika total harga tidak sama
                // maka, mengambil value harga paling tinggi
                $max = max(array_column($ret, 'fitnessValue'));
                // lalu dicocokkan lagi, untuk mengambil index dari value tersebut
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }

            return $ret[$index];
        }
    }

    // method untuk mencari max. item dari individu2 fit
    // serta menghitung selisih total harga dan budget
    function isFound($fits)
    {
        // mengambil value 'numberOfselectedItem' dari array $fits, lalu menyimpan di array_column
        // dengan fungsi array_count_values(), value tadi menjadi index di array baru, lalu value nya menunjukkan jumlah dari index tersebut
        $countedMaxItem = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        print_r($countedMaxItem);
        echo "<br>";

        // karena telah menjadi index, maka digunakan array_keys untuk mengambil nya menjadi value lagi
        // kemudian dicari mana yang paling besar dengan fungsi max()
        $maxItem = max(array_keys($countedMaxItem));
        echo  "Max. Item Paling Besar : " . $maxItem;
        echo '<br>';
        // nilai paling besar kemudian dijadikan index untuk mengambil jumlah individu yang memiliki nilai tersebut
        echo "Jumlah : " . $countedMaxItem[$maxItem];
        echo '<br>';
        // jumlah dari Max Item paling besar ditampung
        $numberOfIndividuHasMaxItem = $countedMaxItem[$maxItem];

        // mengirim argumen berupa array $fits, Max. Item paling besar, jumlah dari max item paling besar
        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
        echo "<br>";
        echo "<br>Best Fitness Value : " . $bestFitnessValue;
   
        // mengecek selisih dari total harga paling tinggi dengan budget
        $residual = Parameters::BUDGET - $bestFitnessValue;
        echo '<br>Residual : ' . $residual;

        // jika selisihnya kurang dari STOPPING_VALUE
        // maka indvidiu yang memenuhi fungsi fitness telah ditemukan
        if ($residual <= Parameters::STOPPING_VALUE && $residual > 0) {
            return TRUE;
        }
    }

    // menerima argumen berupa jumlah total harga satu individu,
    // dan membandingkan dengan BUDGET yang sudah ditetapkan
    function isFit($fitnessValue)
    {
        if ($fitnessValue <= Parameters::BUDGET) {
            return TRUE;
        }
    }

    // method untuk membaca dan menampilkan isi populasi
    function fitnessEvaluation($population){
        $catalogue = new Catalogue;

        // ------------------------- OPERASI FITNESS ---------------------- ++
        // -------------- OUTPUT POPULASI ------------ ++
        // perulangan untuk menampilkan isi populasi dengan sumber class populasi
        foreach($population as $listOfIndividuKey => $listOfIndividu){
            echo "Individu-" . $listOfIndividuKey . "<br>";
            // perulangan untuk menampilkan isi individu dengan sumber class catalogue
            // dan $individuKey sebagai index dari class catalogue, sehingga menampilkan array berisi item dan price
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen . "&nbsp;&nbsp;";
                print_r($catalogue->product()[$individuKey]);
                echo '<br>';
            }
        // -------------- OUTPUT POPULASI ------------ ++

            // menampung total harga dari gen-gen yang terpilih (biner 1)
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            // menampung total gen yang terpilih (biner 1)
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);

            echo "Max. Item : $numberOfSelectedItem ";
            echo "Fitness Value : $fitnessValue";

            // menampilkan status FIT atau TIDAK FIT dari sebuah individu dari method isFit()
            // sekaligus menampung informasi dari individu fit ke array $fits
            if ($this->isFit($fitnessValue)) {
                echo ' (Fit)<br>';
                // array berisi individu2 yang berstatus FIT
                $fits[] = [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue,
                    'chromosome' => $population[$listOfIndividuKey]
                ];
                print_r($fits);
            } else {
                echo ' (Not Fit)<br>';
            }
            echo "<br><br>";
        }
        if ($this->isFound($fits)) {
            echo " Found";
        } else {
            echo " >> Next Generation";
        }
        // ------------------------- OPERASI FITNESS ---------------------- ++
    }
}


// $individu = new Individu;
// print_r($individu->createRandomIndividu());

// $katalog = new Catalogue;
// print_r($katalog->product());

$initialPopulation = new Population;
$initialPopulation = $initialPopulation->createRandomPopulation();

$fitness = new Fitness;
$fitness->fitnessEvaluation($initialPopulation);
