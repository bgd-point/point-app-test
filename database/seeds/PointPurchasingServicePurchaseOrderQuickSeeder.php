<?php

use Illuminate\Database\Seeder;
use Point\Core\Helpers\PermissionHelper;
use Point\Framework\Helpers\FormulirNumberHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class PointPurchasingServicePurchaseOrderQuickSeeder extends Seeder {

  /**
   * @var mixed
   */
  private $output;

  /**
   * @param ConsoleOutput $output
   */
  public function __construct(ConsoleOutput $output) {
    $this->output = $output;
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $group = 'POINT PURCHASING SERVICE';

    PermissionHelper::create('POINT PURCHASING SERVICE PURCHASE ORDER', ['create', 'read', 'update', 'delete', 'approval'], $group);
    $this->output->writeln('<info>--- Permission for purchasing service purchase order inserted ---</info>');
    $this->output->writeln('<info>--- Admin must manually grant himself this permission ---</info>');

    FormulirNumberHelper::create('point-purchasing-service-purchase-order', 'PURCHASING-SERVICE/PO/');
    $this->output->writeln('<info>--- Formulir number for purchasing service purchase order inserted ---</info>');
  }
}
