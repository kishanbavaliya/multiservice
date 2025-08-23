<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\JobHandlerService;
use Spatie\ModelStatus\Status;

class OrderStatusObserver
{



    public function creating(Status $statusModel)
    {
        // logger("called here ==> creating");
        // AppLangService::tempLocale();
        //
        // AppLangService::restoreLocale();
    }

    public function created(Status $statusModel)
    {
        // logger("called here ==> created");
        if (isUsingWebsocket()) {
            $modelId = $statusModel->model->id;
            $order = Order::find($modelId);
            (new JobHandlerService())->pushOrderToFCMJob($order);
        }
    }
}
