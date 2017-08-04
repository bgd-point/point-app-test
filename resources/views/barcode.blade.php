<style>

    .paper {
        width: 99px;
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
        echo $barcode->getBarcodeSVG("1234", "EAN13", 0.8);
        echo '<br> Test';
        ?>
    </span>
    <span class="paper-block-2">
        <?php
        echo $barcode->getBarcodeSVG("1234", "EAN13", 0.8);
        echo '<br> Test';
        ?>
    </span>
</div>

