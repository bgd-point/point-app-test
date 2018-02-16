<style>

    .paper {
        width: 99px;
        font-size: 11px;
    }

    .paper-block-1 {
        position: absolute;
    }

    .paper-block-2 {
        position: absolute;
        left: 99px;
    }
</style>
<?php

use Milon\Barcode\DNS1D;

$barcode = new DNS1D;
?>

<div class="paper">
    <span class="paper-block-1">
        <?php
        echo $barcode->getBarcodeSVG("123456", "c128", 1);
        echo '<br> Test';
        ?>
    </span>
    <span class="paper-block-2">
        <?php
        echo $barcode->getBarcodeSVG("123456", "C128", 1);
        echo '<br> Test';
        ?>
    </span>
</div>

