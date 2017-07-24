<?php

namespace Point\PointManufacture\Vesa;

trait InputVesa
{
    public static function getVesa()
    {
        $array = self::vesaApproval();
        $array = self::vesaCreateOutput($array);
        return $array;
    }

    public static function getVesaApproval()
    {
        return self::vesaApproval([], false);
    }

    public static function getVesaCreateOutput()
    {
        return self::vesaCreateOutput([], false);
    }

    private static function vesaApproval($array = [], $merge_into_group = true)
    {
        $list_input_in = self::joinFormulir()->open()->approvalPending()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_input_in->count() > 5) {
            array_push($array, [
                'url' => url('manufacture/point/process-io/vesa-approval'),
                'deadline' => $list_input_in->formulir->form_date,
                'message' => 'Please approve this manufacture process in number',
                'permission_slug' => 'approval.point.manufacture.input'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_input_in->get() as $input_in) {
            array_push($array, [
                'url' => url('manufacture/point/process-io/' . $input_in->id . '/input/' . $input_in->id),
                'deadline' => $input_in->formulir->form_date,
                'message' => 'Please approve this manufacture process in number ' . $input_in->formulir->form_number,
                'permission_slug' => 'approval.point.manufacture.input'
            ]);
        }

        return $array;
    }

    private static function vesaCreateOutput($array = [], $merge_into_group = true)
    {
        $list_manufacture = self::joinFormulir()->open()->approvalApproved()->notArchived()->selectOriginal();

        // Grouping vesa
        if ($merge_into_group && $list_manufacture->count() > 5) {
            array_push($array, [
                'url' => url('manufacture/point/process-io/vesa-proses-after-approval'),
                'deadline' => $list_manufacture->orderBy('required_date')->first()->required_date,
                'message' => 'Make an manufacture process out',
                'permission_slug' => 'create.point.manufacture.output'
            ]);

            return $array;
        }

        // Push all
        foreach ($list_manufacture->get() as $manufacture) {
            array_push($array, [
                'url' => url('manufacture/point/process-io/' . $manufacture->id . '/output/create-step-2/' . $manufacture->id),
                'deadline' => $manufacture->formulir->form_date,
                'message' => 'Make an manufacture process out from ' . $manufacture->formulir->form_number,
                'permission_slug' => 'create.point.manufacture.output'
            ]);
        }

        return $array;
    }
}
