<?php

namespace App\Traits;

use App\Services\JobHandlerService;
use Spatie\ModelStatus\HasStatuses;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Spatie\ModelStatus\Status;

trait CustomHasStatuses
{
    use HasStatuses;

    public function setStatus(string $name, ?string $reason = null): self
    {
        //convert name
        $name = $this->convertStatusName($name);
        if (!$this->isValidStatus($name, $reason)) {
            throw InvalidStatus::create($name);
        }
        return $this->forceSetStatus($name, $reason);
    }



    public function forceSetStatus(string $name, ?string $reason = null): self
    {
        $oldStatus = $this->latestStatus();
        //MAKE SURE STATUS IS ALLOWED TO CHANGE
        //prevent changing status after delivered,failed,cancelled
        $unAllowedStatuses = ["delivered", "completed", "failed", "fail", "cancelled", "cancel", "success", "successful"];
        $allowedAction = !in_array($oldStatus, $unAllowedStatuses);
        if (!$allowedAction) {
            return $this;
        }
        //convert name
        $name = $this->convertStatusName($name);
        // $newStatus = $this->statuses()->create([
        //     'name' => $name,
        //     'reason' => $reason,
        // ]);

        $newStatus = new Status();
        $newStatus->name = $name;
        $newStatus->reason = $reason;
        $newStatus->model()->associate($this);
        $newStatus->save();
        $this->statuses()->save($newStatus);

        event(new StatusUpdated($oldStatus, $newStatus, $this));
        //for sending out order events
        if (isUsingWebsocket()) {
            (new JobHandlerService())->pushOrderToFCMJob($this);
        }

        return $this;
    }




    public function convertStatusName($name)
    {
        //convert name
        if (in_array($name, ["completed", "complete"])) {
            $name = "delivered";
        }
        return $name;
    }
}