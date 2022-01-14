<?php

// class berisi informasi yang dibutuhkan project
class Parameters {
    const FILE_NAME = 'products.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 30;
    const BUDGET = 280000;
    const STOPPING_VALUE = 10000;
    const CROSSOVER_RATE = 0.8;
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
        // print_r($countedMaxItem);
        // echo "<br>";

        // karena telah menjadi index, maka digunakan array_keys untuk mengambil nya menjadi value lagi
        // kemudian dicari mana yang paling besar dengan fungsi max()
        $maxItem = max(array_keys($countedMaxItem));
        // echo  "Max. Item Paling Besar : " . $maxItem;
        // echo '<br>';
        // // nilai paling besar kemudian dijadikan index untuk mengambil jumlah individu yang memiliki nilai tersebut
        // echo "Jumlah : " . $countedMaxItem[$maxItem];
        // echo '<br>';
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
                // echo $binaryGen . "&nbsp;&nbsp;";
                // print_r($catalogue->product()[$individuKey]);
                // echo '<br>';
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
                    'fitnessValue' => $fitnessValue
                ];
                // print_r($fits);
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

// class untuk melakukan Crossover
class Crossover
{
    public $population;

    function __construct($population)
    {
        $this->population = $population;
    }

    // method untuk mencari nilai acak 0 sampai 1
    function randomZerotoOne()
    {
        return (float) rand() / (float) getrandmax();
    }

    // method untuk membangkitkan nilai acak
    function generateCrossover()
    {
        // membangkitkan nilai acak sebanyak isi populasi
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++) {
            $randomZeroToOne = $this->randomZeroToOne();
            // nilai acak di komparasi dengan CR untuk menentukan individu yang akan di crossover
            if ($randomZeroToOne < Parameters::CROSSOVER_RATE) {
                $parents[$i] = $randomZeroToOne;
            }
        }
        echo "<br>";
        // membuat kombinasi dari individu2 yang terpilih
        foreach (array_keys($parents) as $key) {
            foreach (array_keys($parents) as $subkey) {
                if ($key !== $subkey) {
                    $ret[] = [$key, $subkey];
                }
            }
            array_shift($parents);
        }
        echo "<br>";
        return $ret;
    }

    //method untuk membangkitkan individu baru (offspring)
    function offspring($parent1, $parent2, $cutPointIndex, $offspring)
    {
        $lengthOfGen = new Individu();
        // membuat individu baru (offspring) ke 1
        if ($offspring === 1) {
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen() - 1; $i++) {
                // melakukan pemotongan dengan teknik One Cut-Point
                // menampung value sebelum garis potong ke array
                if ($i <= $cutPointIndex) {
                    $ret[] = $parent1[$i];
                }
                // menampung value setelah garis potong ke array
                if ($i > $cutPointIndex) {
                    $ret[] = $parent2[$i];
                }
            }
        }

         // membuat individu baru (offspring) ke 2
         if ($offspring === 2) {
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen() - 1; $i++) {
                // melakukan pemotongan dengan teknik One Cut-Point
                // menampung value sebelum garis potong ke array
                if ($i <= $cutPointIndex) {
                    $ret[] = $parent2[$i];
                }
                // menampung value setelah garis potong ke array
                if ($i > $cutPointIndex) {
                    $ret[] = $parent1[$i];
                }
            }
        }
        return $ret;
    }

    // method untuk menentukan cut point
    function cutPointRandom()
    {
        $lengthOfGen = new Individu();
        return rand(0, $lengthOfGen->countNumberOfGen() - 1);
    }

    // method induk untuk melakukan dan menampilkan hasil crossover
    function crossover()
    {
        $cutPointIndex = $this->cutPointRandom();
        // echo "Cut Point : " . $cutPointIndex;
        // perulangan untuk menampilkan individu yang terpilih
        foreach ($this->generateCrossover() as $listOfCrossover) {
            // array $listOfCrossover memiliki 2 index, dimana value nya adalah individu yang terpilih
            // value tersebut kemudian dijadikan index ke array populasi untuk mengambil data gen nya
            $parent1 = $this->population[$listOfCrossover[0]];
            $parent2 =  $this->population[$listOfCrossover[1]];

            // echo "<p></p>";
            // echo "Parents : <br>";
            // // perulangan untuk menampilkan value dari parent
            // foreach ($parent1 as $gen) {
            //     echo $gen;
            // }
            // echo " >< ";
            // foreach($parent2 as $gen) {
            //     echo $gen;
            // }

            // echo "<br>";
            // echo "Offspring : <br>";
            // mengambil hasil return dari method offspring
            $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
            $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
            
            // perulangan untuk menampilkan value dari offspring
            // foreach ($offspring1 as $gen) {
            //     echo $gen;
            // }
            // echo " >< ";
            // foreach($offspring2 as $gen) {
            //     echo $gen;
            // }
            $offsprings[] = $offspring1;
            $offsprings[] = $offspring2;
        }
        return $offsprings;
    }
}

// class untuk membangkitkan nilai-nilai acak
class Randomizer
{
    // method untuk menentukan urutan Gen yang akan diambil secara acak
    static function getRandomIndexOfGen()
    {
        return rand(0, (new Individu())->countNumberOfGen() - 1);
    }

    // method untuk menentukan urutan individu yang akan diambil secara acak
    static function getRandomIndexOfIndividu()
    {
        return rand(0, Parameters::POPULATION_SIZE - 1);
    }
}



// class untuk melakukan mutasi
class Mutation
{
    public $population;

    function __construct($population)
    {
        $this->population = $population;
    }

    // method untuk menghitung Mutation Rate
    function calculateMutationRate()
    {
        return 1 / (new Individu())->countNumberOfGen();
    }

    // method untuk menghitung jumlah Mutasi
    function calculateNumOfMutation()
    {
        return round($this->calculateMutationRate() * Parameters::POPULATION_SIZE);
    }

    // method untuk mengecek apakah jumlah mutasi lebih dari 0 atau tidak
    function isMutation() {
        if ($this->calculateNumOfMutation() > 0) {
            return TRUE;
        }
    }

    // method untuk melakukan mutasi value dari gen
    function generateMutation($valueOfGen)
    {
        if ($valueOfGen === 0) {
            return 1;
        } else {
            return 0;
        }
    }

    function mutation()
    {
        if ($this->isMutation()) {
            // melakukan mutasi sebanyak jumlah mutasi
            for ($i = 0; $i <= $this->calculateNumOfMutation()-1; $i++) {
                // menyimpan urutan individu terpilih
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
                // menyimpan urutan gen terpilih
                $indexOfGen = Randomizer::getRandomIndexOfGen();
                // menyimpan individu terpilih
                $selectedIndividu = $this->population[$indexOfIndividu];

                // echo "<p></p>";
                // echo "<br>Before Mutation : ";
                // print_r($selectedIndividu);
                // echo "<br>";

                // menyimpan gen terpilih
                $valueOfGen = $selectedIndividu[$indexOfGen];
                // menyimpan hasil value gen yang telah dimutasi
                $mutatedGen = $this->generateMutation($valueOfGen);
                // menimpa value gen lama dengan gen hasil mutasi
                $selectedIndividu[$indexOfGen] = $mutatedGen;
                // echo "After Mutation : ";
                // print_r($selectedIndividu);
                // echo "<br>";
                // array berisi individu yang sudah dimutasi
                $ret[] = $selectedIndividu;
            }
            return $ret;
        }
        
    }
}

// class selection
class Selection
{
    // konstruktor sebagai jembatan dalam mengambil data populasi dan offspring
    function __construct($population, $combinedOffsprings)
    {
        $this->population = $population;
        $this->combinedOffsprings = $combinedOffsprings;
    }

    //method untuk menggabungkan data populasi dan offspring
    function createTemporaryPopulation()
    {
        foreach ($this->combinedOffsprings as $offspring) {
            $this->population[] = $offspring;
        }
        return $this->population;
    }

    // method untuk menampilkan kembali individu dan gen nya
    function getVariableValue($basePopulation, $fitTemporaryPopulation)
    {
        // mengakses array temporary fit
        foreach($fitTemporaryPopulation as $val) {
            // index 1 pada array temporary fit adalah urutan individu
            // urutan individu kemudian menjadi index pada array temporary populasi gabungan,
            // untuk mencari data individu tersebut
            // maka akan didapatkan individu beserta gen nya, kemudian disimpan di array ret[]
            $ret[] = $basePopulation[$val[1]];
        }
        return $ret;
    }

    // method untuk melakukan pengurutan dan beberapa langkah selection
    function sortFitTemporaryPopulation()
    {
        $tempPopulation = $this->createTemporaryPopulation();
        $fitness = new Fitness();
        // mengakses array temporay population
        foreach($tempPopulation as $key => $individu) {
            // memanggil method untuk menghitung jumlah fitness value dengan
            // mengirim individu sebagai parameter
            $fitnessValue = $fitness->calculateFitnessValue($individu);
            // kemudian melakukan pengecekan status dari fitness value sebuah individu
            // jika fit, maka akan disimpan di array temporary
            if($fitness->isFit($fitnessValue)) {
                $fitTemporaryPopulation[] = [
                    $fitnessValue,
                    $key
                ];
            }
        }
         // mengurutkan array temporary bersisi indivdu fit, dengan metode descending
         rsort($fitTemporaryPopulation);
         // mengambil individu hanya sebanyak jumlah individu di populasi awal
         // dengan memotong array temporary fit menggunakan fungsi array slice
         $fitTemporaryPopulation = array_slice($fitTemporaryPopulation, 0, Parameters::POPULATION_SIZE);
         return $this->getVariableValue($tempPopulation, $fitTemporaryPopulation);
    }

    // method untuk menampilkan output selection
    function selectingIndividu()
    {
        $selected = $this->sortFitTemporaryPopulation();
        echo "<p></p>";
        print_r($selected);
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

$crossover = new Crossover($initialPopulation);
$crossoverOffsprings = $crossover->crossover();

// echo 'Crossover Offsprings : <br>';
// print_r($crossoverOffsprings);

$mutation = new Mutation($initialPopulation);
if ($mutation->mutation()) {
    $mutationOffsprings = $mutation->mutation();
    // echo '<br>Mutation Offsprings : <br>';
    // print_r($mutationOffsprings);
    // echo "<p></p>";
    foreach ($mutationOffsprings as $mutationOffspring) {
        $crossoverOffsprings[] = $mutationOffspring;
    }
}

// echo '<br>Mutation Offsprings : <br>';
// // print_r($crossoverOffsprings);

$selection = new Selection($initialPopulation, $crossoverOffsprings);
$selection->selectingIndividu();
