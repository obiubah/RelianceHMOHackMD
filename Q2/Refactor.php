<?php


class Refactor
{

    //I can't seem to figure out a way to reduce the time complexity....... Seems more like a laravel or SQL hack would do the trick

    function getStatistics() {
        $data = [];
        $data['users'] = [];
        // 65k rows
        $allTptp = TariffProviderTariffMatch::all();
        foreach ($allTptp->groupBy('user_id') as $each) {
            $one = [];
            $one['name'] = $each[0]->user->first_name . " " . $each[0]->user->last_name;
            $one['valid'] = 0;
            $one['pending'] = 0;
            $one['invalid'] = 0;
            $one['total'] = 0;
            $one['cash'] = 0;
            foreach ($each as $single) {
                switch ($single->active_status) {
                    case ActiveStatus::ACTIVE: // 1
                        $one['valid']++;
                        $one['cash'] += floatval(GlobalVariable::getById(GlobalVariable::STANDARDIZATION_UNIT_PRICE)->value);
                        break;
                    case ActiveStatus::PENDING: // 2
                        $one['pending']++;
                        break;
                    case ActiveStatus::DELETED: // 3
                        $one['invalid']++;
                        break;
                }
                $one['total']++;
            }
            $one['cash'] = number_format($one['cash'],2);
            array_push($data['users'], $one);
        }
        return $data;
    }
}