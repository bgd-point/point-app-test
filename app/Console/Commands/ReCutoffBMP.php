<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Point\Framework\Models\Formulir;
use Point\Framework\Models\Inventory;
use Point\Framework\Models\Journal;
use Point\Framework\Models\Master\Coa;
use Point\Framework\Helpers\FormulirHelper;
use Point\Framework\Helpers\InventoryHelper;
use Point\Framework\Helpers\JournalHelper;
use Point\Framework\Models\Master\Allocation;
use Point\Framework\Models\Master\Item;
use Point\Framework\Models\Master\Person;
use Point\Framework\Models\Master\Warehouse;
use Point\PointInventory\Models\StockOpname\StockOpname;
use Point\PointInventory\Models\StockOpname\StockOpnameItem;
use Point\PointInventory\Models\TransferItem\TransferItem;
use Point\PointAccounting\Models\CutOffAccount;
use Point\PointAccounting\Models\CutOffAccountDetail;
use Point\PointAccounting\Models\CutOffInventoryDetail;
use Point\PointAccounting\Models\CutOffPayable;
use Point\PointAccounting\Models\CutOffPayableDetail;
use Point\PointAccounting\Models\CutOffReceivable;
use Point\PointAccounting\Models\CutOffReceivableDetail;
use RuntimeException;
use JsonException;

class ReCutoffBMP extends Command
{
  /**
   * The name and signature of the console command.
   * php artisan dev:recalculate:cutoff-bmp && dev:recalculate:mj-bmp && php artisan dev:recutoff:bmp
   *
   * @var string
   */
  protected $signature = 'dev:recutoff:bmp';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'recalculate bmp';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $this->comment('recalculating bbf');

    try {
      $filePath = 'cutoff_accounts.json';
      
      if (!Storage::disk('local')->exists($filePath)) {
        throw new RuntimeException(
          "JSON file not found: {$filePath}"
        );
      }

      $json = Storage::disk('local')->get($filePath);

      $accounts = json_decode($json, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException(
          'Invalid JSON: ' . json_last_error_msg()
        );
      }
   
      if (!is_array($accounts) || empty($accounts)) {
        throw new RuntimeException(
            'The JSON file must contain a non-empty array of accounts.'
        );
      }

      // foreach ($accounts as $account) {
      //   $this->comment($account['coa_code']);
      //   $coa = Coa::where('coa_number', $account['coa_code'])->first();
      //   $this->comment($account['coa_code'] . ' = ' . $coa->coa_number);
      // }

      // Cutoff - Accounts
      CutOffAccountDetail::where('cut_off_account_id', 8)->delete();
      foreach ($accounts as $account) {
          $coa = Coa::where(
              'coa_number',
              $account['coa_code']
          )->first();

          if (!$coa) {
              throw new RuntimeException(
                  "COA not found: {$account['coa_code']}"
              );
          }

          $cutOffAccountDetail = new CutOffAccountDetail;
          $cutOffAccountDetail->cut_off_account_id = 8;
          $cutOffAccountDetail->coa_id = $coa->id;
          $cutOffAccountDetail->debit = $account['debit'];
          $cutOffAccountDetail->credit = $account['credit'];
          $cutOffAccountDetail->save();
      }

      // Cutoff - Inventories

      $filePath = 'cutoff_inventories.json';
      
      if (!Storage::disk('local')->exists($filePath)) {
        throw new RuntimeException(
          "JSON file not found: {$filePath}"
        );
      }

      $json = Storage::disk('local')->get($filePath);

      $accounts = json_decode($json, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException(
          'Invalid JSON: ' . json_last_error_msg()
        );
      }
   
      if (!is_array($accounts) || empty($accounts)) {
        throw new RuntimeException(
            'The JSON file must contain a non-empty array of accounts.'
        );
      }

      CutOffInventoryDetail::where('cut_off_inventory_id', 44)->delete();
      foreach ($accounts as $account) {
          $item = Item::where('code', $account['subledger_code'])->first();
          if (!$item) {
              throw new RuntimeException(
                  "Item not found: {$account['code']}"
              );
          }
          $warehouse = Warehouse::where('name', $account['warehouse_name'])->first();
          if (!$warehouse) {
              throw new RuntimeException(
                  "Warehouse not found: {$account['warehouse_name']}"
              );
          }

          $ci = new CutOffInventoryDetail;
          $ci->cut_off_inventory_id = 44;
          $ci->warehouse_id = $warehouse->id;
          $ci->subledger_type = 'Point\Framework\Models\Master\Item';
          $ci->subledger_id = $item->id;
          $ci->coa_id = $item->account_asset_id;
          $ci->stock_in_database = 0;
          $ci->stock = $account['quantity'];
          $ci->amount = $account['amount'];
          $ci->notes = isset($account['notes']) ? $account['notes'] : '';
          $ci->save();
      }

      // Cutoff - Assets

      $filePath = 'cutoff_assets.json';
      
      if (!Storage::disk('local')->exists($filePath)) {
        throw new RuntimeException(
          "JSON file not found: {$filePath}"
        );
      }

      $json = Storage::disk('local')->get($filePath);

      $accounts = json_decode($json, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException(
          'Invalid JSON: ' . json_last_error_msg()
        );
      }
   
      if (!is_array($accounts) || empty($accounts)) {
        throw new RuntimeException(
            'The JSON file must contain a non-empty array of accounts.'
        );
      }

      foreach ($accounts as $account) {
          $item = Item::where('code', $account['subledger_code'])->first();
          if (!$item) {
              throw new RuntimeException(
                  "Item not found: {$account['code']}"
              );
          }
          $warehouse = Warehouse::where('name', $account['warehouse_name'])->first();
          if (!$warehouse) {
              throw new RuntimeException(
                  "Warehouse not found: {$account['warehouse_name']}"
              );
          }

          $coa = Coa::where('coa_number', $account['coa_number'])->first();
          if (!$coa) {
              throw new RuntimeException(
                  "Warehouse not found: {$account['coa_number']}"
              );
          }

          $ci = new CutOffInventoryDetail;
          $ci->cut_off_inventory_id = 44;
          $ci->warehouse_id = $warehouse->id;
          $ci->subledger_type = 'Point\Framework\Models\Master\Item';
          $ci->subledger_id = $item->id;
          $ci->coa_id = $coa->id;
          $ci->stock_in_database = 0;
          $ci->stock = $account['quantity'];
          $ci->amount = $account['amount'];
          $ci->notes = isset($account['notes']) ? $account['notes'] : '';
          $ci->save();
      }


      // Cutoff - Receivable

      $filePath = 'cutoff_receivables.json';
      
      if (!Storage::disk('local')->exists($filePath)) {
        throw new RuntimeException(
          "JSON file not found: {$filePath}"
        );
      }

      $json = Storage::disk('local')->get($filePath);

      $accounts = json_decode($json, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException(
          'Invalid JSON: ' . json_last_error_msg()
        );
      }
   
      if (!is_array($accounts) || empty($accounts)) {
        throw new RuntimeException(
            'The JSON file must contain a non-empty array of accounts.'
        );
      }

      CutOffReceivableDetail::where('cut_off_receivable_id', 10)->delete();
      foreach ($accounts as $account) {

          if ($account['subledger_code']) {
            $person = Person::where('code', $account['subledger_code'])->first();
            if (!$person) {
                throw new RuntimeException(
                    "Person not found: {$account['subledger_code']}"
                );
            }
          }
          
          $coa = Coa::where('coa_number', $account['coa_number'])->first();
          if (!$coa) {
              throw new RuntimeException(
                  "Warehouse not found: {$account['coa_number']}"
              );
          }

          $ci = new CutOffReceivableDetail;
          $ci->cut_off_receivable_id = 10;
          $ci->coa_id = $coa->id;
          $ci->subledger_type = isset($account['subledger_code']) ? 'Point\Framework\Models\Master\Person' : NULL;
          $ci->subledger_id = isset($account['subledger_code']) ? $person->id : NULL;
          $ci->amount = $account['amount'];
          $ci->notes = isset($account['notes']) ? $account['notes'] : '';
          $ci->save();
      }

      // Cutoff - Payable

      $filePath = 'cutoff_payables.json';
      
      if (!Storage::disk('local')->exists($filePath)) {
        throw new RuntimeException(
          "JSON file not found: {$filePath}"
        );
      }

      $json = Storage::disk('local')->get($filePath);

      $accounts = json_decode($json, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException(
          'Invalid JSON: ' . json_last_error_msg()
        );
      }
   
      if (!is_array($accounts) || empty($accounts)) {
        throw new RuntimeException(
            'The JSON file must contain a non-empty array of accounts.'
        );
      }

      CutOffPayableDetail::where('cut_off_payable_id', 15)->delete();
      foreach ($accounts as $account) {

          if ($account['subledger_code']) {
            $person = Person::where('code', $account['subledger_code'])->first();
            if (!$person) {
                throw new RuntimeException(
                    "Person not found: {$account['subledger_code']}"
                );
            }
          }
          
          $coa = Coa::where('coa_number', $account['coa_number'])->first();
          if (!$coa) {
              throw new RuntimeException(
                  "Warehouse not found: {$account['coa_number']}"
              );
          }

          $ci = new CutOffPayableDetail();
          $ci->cut_off_payable_id = 15;
          $ci->coa_id = $coa->id;
          $ci->subledger_type = isset($account['subledger_code']) ? 'Point\Framework\Models\Master\Person' : NULL;
          $ci->subledger_id = isset($account['subledger_code']) ? $person->id : NULL;
          $ci->amount = $account['amount'];
          $ci->notes = isset($account['notes']) ? $account['notes'] : '';
          $ci->save();
      }

      DB::commit();

      $this->info('BBF recalculation completed successfully.');
    } catch (JsonException | RuntimeException $exception) {
      DB::rollBack();

      $this->error($exception->getMessage());
    } catch (\Throwable $exception) {
      DB::rollBack();

      throw $exception;
    }
  }
}
