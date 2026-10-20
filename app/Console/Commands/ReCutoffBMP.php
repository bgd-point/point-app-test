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
          $warehouse = Warehouse::where('name', $account['warehouse_name'])->first();

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

      DB::commit();

      $this->info('BBF recalculation completed successfully.');
    } catch (JsonException | RuntimeException $exception) {
      DB::rollBack();

      $this->error($exception->getMessage());
    } catch (\Throwable $exception) {
      DB::rollBack();

      throw $exception;
    }

    \DB::beginTransaction();

    $cutoff_account_csv = '';

    // $form_date = '2026-08-01 00:00:00';

    // $form_number = FormulirHelper::number('point-accounting-cut-off-account', $form_date);

    // $formulir = new Formulir;
    // $formulir->form_date = $form_date;
    // $formulir->form_number = $form_number['form_number'];
    // $formulir->form_raw_number = $form_number['raw'];
    // $formulir->notes = '';
    // $formulir->approval_to = 1;
    // $formulir->approval_status = 0;
    // $formulir->approval_message = '';
    // $formulir->created_by = 1;
    // $formulir->updated_by = 1;
    // $formulir->save();

    // $cut_off_account = new CutOffAccount;
    // $cut_off_account->formulir_id = $formulir->id;
    // $cut_off_account->save();

    // $coa = Coa::where('coa_code', $account['coa_code'])->first();

    // foreach ($cutoff_account_csv as $account) {
    //   $cut_off_account_detail = new CutOffAccountDetail;
    //   $cut_off_account_detail->cut_off_account_id = $cut_off_account->id;
    //   $cut_off_account_detail->coa_id = $coa->id;
    //   $cut_off_account_detail->debit = $account->debit;
    //   $cut_off_account_detail->credit = $account->credit;
    //   $cut_off_account_detail->save();
    // }

    \DB::commit();
  }
}
