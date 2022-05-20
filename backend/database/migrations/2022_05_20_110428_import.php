<?php

use App\Models\Prices;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Import extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Read csv file
        if (($handle = fopen(__DIR__."../../resources/import.csv", "r")) !== FALSE) 
        {
            $row = 0;

            //Used for dynamic search, for no specific column order
            $headers = [];

            $insert = [];
            
            //Read line
            while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) 
            {                
                $rowData = [];

                //Read each column
                for ($index = 0; $index < count($line); $index++) 
                {
                    $data = $line[$index];

                    //Assuming the first line are the headers
                    if($row == 0)
                    {
                        //Store the header's index as well its mapped name
                        $headers[$index] = $data;
                        continue;
                    }

                    $rowData[$headers[$index]] = $data != '' ? $data : null;
                }

                $row++;
                if($row == 1) continue;

                array_push($insert, $rowData);
            }

            fclose($handle);

            DB::statement('CREATE TABLE IF NOT EXISTS `import_tmp` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `sku` text,
                    `account_ref` text,
                    `user_ref` text,
                    `quantity` int(11) DEFAULT NULL,
                    `value` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
            ');

            foreach ($insert as $value)
                DB::table('import_tmp')->insert($value);

            DB::statement("INSERT into prices (product_id, user_id, account_id, quantity, value, created_at, updated_at) SELECT 
                    p.id as product_id, 
                    u.id as user_id, 
                    a.id as account_id ,
                    it.quantity,
                    it.value,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM `import_tmp` it
                left join products p on p.sku = it.sku
                left join users u on u.external_reference = it.user_ref
                left join accounts a on a.external_reference = it.account_ref
                group by it.sku, it.user_ref, it.account_ref
                order by it.sku, it.user_ref, it.account_ref;"
            );

            DB::statement('DROP TABLE IF EXISTS `import_tmp`');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Prices::truncate();
        DB::statement('DROP TABLE IF EXISTS `import_tmp`');
    }
}
