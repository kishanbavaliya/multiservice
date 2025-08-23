<?php

namespace App\Http\Livewire\Report;

use App\Http\Livewire\BaseLivewireComponent;
use App\Models\Subscription;
use App\Models\SubscriptionVendor;
use App\Models\Vendor;
use Asantibanez\LivewireCharts\Charts\LivewirePieChart;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Illuminate\Support\Facades\DB;

class SubscriptionReportLivewire extends BaseLivewireComponent
{

    //
    public $model = Subscription::class;

    public function render()
    {

        return view('livewire.reports.subscriptions_report', [
            "topVendorsChart" => $this->topVendorsChart(),
            "topSubscriptionVendorsChart" => $this->topSubscriptionVendorsChart(),
            "leastSubscriptionVendorsChart" => $this->leastVendorsChart(),
        ]);
    }


    public function topVendorsChart()
    {
        //
        $chart = (new LivewirePieChart());
        // $dataSet = SubscriptionVendor::orderByPowerJoinsCount('vendor.id', 'desc')->limit(10)->get();
        // $dataSet = SubscriptionVendor::select(
        //     'vendor_id',
        //     DB::raw('COUNT(*) as subscription_count')
        // )
        //     ->with('vendor:id,name')
        //     ->groupBy('vendor_id')
        //     ->orderBy('subscription_count', 'desc')
        //     ->limit(10)
        //     ->get();
        $dataSet = Vendor::withCount('subscriptions')
            ->having('subscriptions_count', '>', 0)
            ->orderBy('subscriptions_count', 'desc')
            ->limit(10)
            ->get();
        $chart = $dataSet->reduce(
            function ($pieChartModel, $data) {
                return $pieChartModel->addSlice($data['name'] ?? "##Undefined##", $data["subscriptions_count"] ?? 0, $this->genColor());
            },
            LivewireCharts::pieChartModel()
                ->setTitle(__("Top Vendor by no of Subscriptions"))
                ->setAnimated(true)
                ->legendPositionBottom()
                ->legendHorizontallyAlignedCenter()
                ->setDataLabelsEnabled(false)
        );
        return $chart;
    }

    public function topSubscriptionVendorsChart()
    {
        //
        $chart = (new LivewirePieChart());
        // $dataSet = SubscriptionVendor::orderByPowerJoinsCount('subscription.id', 'desc')->limit(10)->get();
        $dataSet = Subscription::withCount('vendors')->orderBy('vendors_count', 'desc')->limit(10)->get();
        $chart = $dataSet->reduce(
            function ($pieChartModel, $data) {
                return $pieChartModel->addSlice($data["name"] ?? "##Undefined##", $data["vendors_count"] ?? 0, $this->genColor());
            },
            LivewireCharts::pieChartModel()
                ->setTitle(__("Top Subscription"))
                ->setAnimated(true)
                ->legendPositionBottom()
                ->legendHorizontallyAlignedCenter()
                ->setDataLabelsEnabled(false)
        );
        return $chart;
    }

    public function leastVendorsChart()
    {
        //
        $chart = (new LivewirePieChart());
        // $dataSet = SubscriptionVendor::orderByPowerJoinsCount('subscription.id', 'asc')->limit(10)->get();
        // $dataSet = SubscriptionVendor::select(
        //     'vendor_id',
        //     DB::raw('COUNT(*) as subscription_count')
        // )
        //     ->with('vendor:id,name')
        //     ->groupBy('vendor_id')
        //     ->orderBy('subscription_count', 'asc')

        $dataSet = Vendor::withCount('subscriptions')
            ->having('subscriptions_count', '>', 0)
            ->orderBy('subscriptions_count', 'asc')
            ->limit(10)
            ->get();
        $chart = $dataSet->reduce(
            function ($pieChartModel, $data) {
                return $pieChartModel->addSlice($data->name ?? "##Undefined##", $data["subscriptions_count"] ?? 0, $this->genColor());
            },
            LivewireCharts::pieChartModel()
                ->setTitle(__("Least Subscription"))
                ->setAnimated(true)
                ->legendPositionBottom()
                ->legendHorizontallyAlignedCenter()
                ->setDataLabelsEnabled(false)
        );
        return $chart;
    }
}