<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class OrderLivewire extends BaseLivewireComponent
{
    //
    public $model = Order::class;
    //
    public $orderId;
    public $deliveryBoyId;
    public $status;
    public $paymentStatus;
    public $note;
    public $isPickup;



    public function getListeners()
    {
        return $this->listeners + [
            'autocompleteDeliveryAddressSelected' => 'autocompleteDeliveryAddressSelected',
            'deliveryBoyIdUpdated' => 'autocompleteDriverSelected',
        ];
    }

    public function render()
    {
        return view('livewire.orders');
    }




    public function autocompleteDriverSelected($value)
    {
        try {
            //clear old products
            $this->deliveryBoyId = $value['value'];
        } catch (\Exception $ex) {
            logger("Error", [$ex]);
        }
    }

    public function showDetailsModal($id)
    {
        $this->selectedModel = $this->model::find($id);
        //prevent none admin from editing unpaid order
        $isAdmin = \Auth::user()->hasRole("admin");
        if (!$isAdmin) {
            //
            $isPharmacyOrder = $this->selectedModel->vendor->vendor_type->slug == "pharmacy";
            if (!$isPharmacyOrder) {
                $paymentMethodSlug = $this->selectedModel->payment_method->slug;
                $isUnpaidOrder = $this->selectedModel->payment_status != "successful" && !in_array($paymentMethodSlug, ["cash", "wallet"]);
                $canViewUnpaid = \Auth::user()->can('handle-unpaid-order');
                if ($isUnpaidOrder && !$canViewUnpaid) {
                    $msg = __("Unpaid Order can't be viwed. Please wait until payment is confirmed");
                    $this->showErrorAlert($msg);
                    return;
                }
            }
        }

        //
        $this->orderId = $id;
        $this->showDetails = true;
        $this->stopRefresh = true;
    }

    // Updating model
    public function initiateEdit($id)
    {
        $this->selectedModel = $this->model::find($id);
        //prevent none admin from editing unpaid order
        $isAdmin = \Auth::user()->hasRole("admin");
        if (!$isAdmin) {
            $isPharmacyOrder = $this->selectedModel->vendor->vendor_type->slug == "pharmacy";
            if (!$isPharmacyOrder) {
                $paymentMethodSlug = $this->selectedModel->payment_method->slug;
                $isUnpaidOrder = $this->selectedModel->payment_status != "successful" && !in_array($paymentMethodSlug, ["cash", "wallet"]);
                $canViewUnpaid = \Auth::user()->can('handle-unpaid-order');
                if ($isUnpaidOrder && !$canViewUnpaid) {
                    $msg = __("Unpaid Order can't be edited. Please wait until payment is confirmed");
                    $this->showErrorAlert($msg);
                    return;
                }
            }
        }


        //
        $this->deliveryBoyId = $this->selectedModel->driver_id;
        $this->status = $this->selectedModel->status;
        $this->paymentStatus = $this->selectedModel->payment_status;
        $this->note = $this->selectedModel->note;
        //
        if ($this->deliveryBoyId != null) {
            $this->emit('deliveryBoyId_Loaded', $this->deliveryBoyId);
        }
        $this->showEditModal();
    }


    public function update()
    {

        try {

            DB::beginTransaction();
            $this->selectedModel->driver_id = $this->deliveryBoyId ?? null;
            $this->selectedModel->payment_status = $this->paymentStatus;
            $this->selectedModel->reason = $this->note;
            $this->selectedModel->save();
            $mStatus = $this->selectedModel->convertStatusName($this->status);
            $this->selectedModel->setStatus($mStatus);
            //if taxi order manual driver assign
            if ($this->selectedModel->driver_id != null && $this->selectedModel->taxi_order != null && $this->selectedModel->status == "pending") {
                $mStatus = "preparing";
                $this->selectedModel->setStatus($mStatus);
            }
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Order") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Order") . " " . __('updated failed!'));
        }
    }



    //reivew payment
    public function reviewPayment($id)
    {
        //
        $this->selectedModel = $this->model::find($id);
        $this->emit('showAssignModal');
    }

    public function approvePayment()
    {
        //
        try {

            DB::beginTransaction();
            $this->selectedModel->payment_status = "successful";
            $this->selectedModel->save();
            //TODO - Issue fetch payment when prescription is been edited
            $this->selectedModel->payment->status = "successful";
            $this->selectedModel->payment->save();
            DB::commit();

            $this->dismissModal();
            $this->reset();
            $this->showSuccessAlert(__("Order") . " " . __('updated successfully!'));
            $this->emit('refreshTable');
        } catch (Exception $error) {
            DB::rollback();
            $this->showErrorAlert($error->getMessage() ?? __("Order") . " " . __('updated failed!'));
        }
    }

    //
    public function showEditOrderProducts()
    {
        $this->closeModal();
        //only allow cod payment edit orders
        if ($this->selectedModel->payment_method != null && !$this->selectedModel->payment_method->is_cash) {
            $this->showErrorAlert(__("Only Order with Cash Payment can be edited. Thank you"));
        } else {
            $link = route('order.edit.products', [
                "code" => $this->selectedModel->code,
            ]);
            $this->emit('newTab', $link);
        }
    }






    //
    public function rejectOrder()
    {
        $this->selectedModel->setStatus('failed');
        $this->selectedModel->refresh();
        $this->dismissModal();
        $this->reset();
        $this->emit('refreshTable');
        $msg = __("Order") . " " . __('rejected successfully!');
        $this->showSuccessAlert($msg);
    }
    public function acceptOrder()
    {
        $this->selectedModel->setStatus('preparing');
        $this->selectedModel->refresh();
        $this->dismissModal();
        $this->reset();
        $this->emit('refreshTable');
        $msg = __("Order") . " " . __('accepted successfully!');
        $this->showSuccessAlert($msg);
    }




    //MISC.
    public function getOrderStatusProperty()
    {
        $order = $this->selectedModel;
        if ($order == null) {
            return [];
        }
        //for pickup order
        $isPickup = $order->delivery_address_id == null && $order->taxi_order == null && ($order->stops->empty() ?? true);
        if ($isPickup) {
            $options = ['scheduled', 'pending', 'preparing', 'ready',   'completed', 'failed', 'cancelled'];
        } else {
            $options =  ['scheduled', 'pending', 'preparing', 'ready', 'enroute', 'delivered', 'failed', 'cancelled'];
        }

        //remove schedule if order pickup_date == null
        if ($order->pickup_date == null) {
            unset($options[0]);
            $options = array_values($options);
        }


        $statuses = [];
        foreach ($options as $value) {
            $statuses[] = [
                "id" => $value,
                "name" => __($value),
            ];
        }
        return $statuses;
    }

    public function getOrderPaymentStatusProperty()
    {
        $options = ['pending', 'request', 'review', 'failed', 'cancelled', 'successful'];
        $statuses = [];
        foreach ($options as $value) {
            $statuses[] = [
                "id" => $value,
                "name" => __($value),
            ];
        }
        return $statuses;
    }

    // public function loadCustomData()
    // {
    //     if (empty($this->orderStatus)) {
    //         $this->orderStatus = $this->orderStatus();
    //     }
    //     if (empty($this->orderPaymentStatus)) {
    //         $this->orderPaymentStatus = $this->orderPaymentStatus();
    //     }
    // }
}