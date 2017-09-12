<?php

namespace Point\BumiShares\Helpers;

use Point\BumiShares\Models\StockFifo;
use Point\Core\Exceptions\PointException;

class SharesHelper {

	public static function searchSellReport($date_from, $date_to, $shares_id) {
		$list_data = StockFifo::joinFormulirSell()->joinSell()
			->where(function ($query) use ($shares_id) {
				if ($shares_id) {
					$query->where('bumi_shares_sell.shares_id', $shares_id);
				};
			})
			->where(function ($query) use ($date_from, $date_to) {
				if ($date_from) {
					if ($date_from) {
			            $query->where('formulir.form_date', '>=', date_format_db($date_from, 'start'));
			        }

			        if ($date_to) {
			            $query->where('formulir.form_date', '<=', date_format_db($date_to, 'end'));
			        }
				}
			});

		return $list_data;
	}
}