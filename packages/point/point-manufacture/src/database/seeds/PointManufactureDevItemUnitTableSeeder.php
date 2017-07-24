<?php

use Illuminate\Database\Seeder;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\OpeningInventory;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class PointManufactureDevItemUnitTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $output = new Output;

        $output->writeln('<info>--- Item Unit Seeder Started ---</info>');

        DB::table('item_unit')->truncate();

        $unit_name = ['kg', 'kg', 'kg', 'biji', 'kg', 'kaleng', 'biji', 'unit', 'kaleng', 'kaleng', 'biji'];

        $quantity = [100, 100, 100, 80, 80, 75, 100, 80, 45, 15, 10];

        $price = [1250, 1700, 1500, 1250, 1200, 1100, 700, 4000, 5000, 4500, 5000];

        $x = 0;

        for ($i = 0; $i < count($unit_name); $i++) {
            DB::table('item_unit')->insert([
                'item_id' => $x + 1,
                'name' => $unit_name[$i],
                'converter' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $formulir = new Formulir;
            $formulir->form_date = date_format_db(date('Y-m-d H:i:s'));
            $formulir->form_number = FormulirHelper::number('opening-inventory', date_format_db(date('Y-m-d H:i:s')));
            $formulir->approval_to = 1;
            $formulir->approval_status = 1;
            $formulir->form_status = 1;
            $formulir->created_by = 1;
            $formulir->updated_by = 1;
            $formulir->save();

            $opening_inventory = new OpeningInventory;
            $opening_inventory->formulir_id = $formulir->id;
            $opening_inventory->item_id = $x + 1;
            $opening_inventory->unit = $unit_name[$i];
            $opening_inventory->save();

            $formulir->formulirable_type = get_class($opening_inventory);
            $formulir->formulirable_id = $opening_inventory->id;
            $formulir->save();

            $inventory = new Inventory();
            $inventory->formulir_id = $formulir->id;
            $inventory->item_id = $x + 1;
            $inventory->quantity = $quantity[$i] * 1;
            $inventory->price = $price[$i] / 1;
            $inventory->form_date = date('Y-m-d H:i:s');
            $inventory->warehouse_id = 1;

            $inventory_helper = new InventoryHelper($inventory);
            $inventory_helper->in();

            $x++;
        }

        $output->writeln('<info>--- Item Unit Seeder Finished ---</info>');
    }
}
