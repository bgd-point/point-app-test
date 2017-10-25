<style>
    /* width 9.906 cm
    heigh 1.4986 cm */

    @page {
        margin-left: 0cm;
        margin-right: 0cm;
    }

    .paper {
        width: 10cm;
        font-size: 11px;
    }

    .paper-block {
        text-align: center;
        float: left;
        width: 33.333%;
    } 
</style>
<?php

use Milon\Barcode\DNS1D;

$barcode = new DNS1D;
?>

<div class="paper">
    <?php $h = 1; $t=''; $mt=''; ?>
    @foreach($invoice->items as $invoice_item)
        <?php
        $item = $invoice_item->item;
        if (! $item->barcode) {
            continue;
        }
        
        $t = $t;
        $mt = $mt;
        if ($h == 1) {
            $t = 1;
            $mt = '';
        }
        ?>
        @for($y=0; $y < $invoice_item->quantity; $y++)
            <?php
            if ($t > 3) {
                $t = 1;
                $mt = 'margin-top:10px';
            }
            ?>
            <span class="paper-block" style="{{$mt}}">
                <?php
                echo $barcode->getBarcodeSVG($item->barcode, "c128", 1);
                echo '<br> '.strtoupper($item->barcode);
                ?>
            </span>
            <?php $t++;?>
        @endfor
    <?php $h++;?>
    @endforeach
</div>

<script type="text/javascript">
      window.onload = function() { window.print(); }
 </script>

