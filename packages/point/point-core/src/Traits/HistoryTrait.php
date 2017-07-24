<?php

namespace Point\Core\Traits;

use Point\Core\Models\Master\History;

trait HistoryTrait
{
    public function save(array $options = [])
    {
        $this->setHistory();
        return parent::save();
    }

    private function setHistory()
    {
        $attributes = $this->getAttributes();
        $original = $this->getOriginal();

        if (!$original || count($original) == 2) {
            return false;
        }

        if (auth()->user()) {
            foreach ($attributes as $key => $value) {
                if ($original[$key] != $value) {
                    if ($key == 'created_by'
                        || $key == 'updated_by'
                        || $key == 'remember_token'
                        || $key == 'password'
                        || $key == 'slug'
                    ) {
                    } elseif ($key == 'password') {
                        $history = new History;
                        $history->history_table = $this::getTable();
                        $history->history_id = $original['id'];
                        $history->user_id = auth()->user()->id;
                        $history->key = $key;
                        $history->old_value = '****';
                        $history->new_value = '****';
                        $history->save();
                    } else {
                        $history = new History;
                        $history->history_table = $this::getTable();
                        $history->history_id = $original['id'];
                        $history->user_id = auth()->user()->id;
                        $history->key = $key;
                        $history->old_value = $original[$key];
                        $history->new_value = $value;
                        $history->save();
                    }
                }
            }
        }
    }
}
