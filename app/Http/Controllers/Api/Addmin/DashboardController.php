<?php

namespace App\Http\Controllers\Api\Addmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Story;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon;
use DB;

class DashboardController extends Controller
{
    public function count_category_story()
    {
        $categories = Category::get();
        $categoryData = [];

        foreach ($categories as $category) {
            $storyCount = Story::where('category_id', $category->id)->count();
            $categoryData[] = [
                'category_name' => $category->category_name,
                'story_count' => $storyCount
            ];
        }

        echo json_encode($categoryData);
    }

    public function recent_transection()
    {
        $subscrition = Subscription::with('user', 'package')->orderBy('id', 'desc')->paginate(8);
        if ($subscrition) {
            return response()->json([
                'status' => 'success',
                'data' => $subscrition
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ]);
        }
    }

    public function transetion_details($id)
    {
        $subscrition = Subscription::where('id', $id)->with('user', 'package')->first();
        if ($subscrition) {
            return response()->json([
                'status' => 'success',
                'data' => $subscrition
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ]);
        }
    }

    // public function income()
    // {
    //     $total_income = Subscription::sum('amount');

    //     $dailyEarning = Subscription::whereDate('created_at', Carbon::today())
    //         ->select(DB::raw('SUM(amount) as dayly_income'))
    //         ->get();

    //     // WEEKLY TOTAL INCOME //

    //     $weeklyTotalSum = Subscription::select(
    //         DB::raw('(SUM(amount)) as total_amount')
    //     )
    //         ->whereYear('created_at', date('Y'))
    //         ->get()
    //         ->sum('total_amount');

    //     // MONTHLY TOTAL INCOME //

    //     $monthlySumAmount = Subscription::whereYear('created_at', date('Y'))
    //         ->whereMonth('created_at', date('n'))
    //         ->sum('amount');

    //     // YEARLY TOTAL INCOME //

    //     $yearlySumAmount = Subscription::whereYear('created_at', date('Y'))
    //         ->sum('amount');

    //     return response()->json([
    //         'total_income' => $total_income,
    //         'daily_income' => $dailyEarning,
    //         'weekly_income' => $weeklyTotalSum,
    //         'monthly_income' => $monthlySumAmount,
    //         'yearly_income' => $yearlySumAmount
    //     ]);
    // }

    public function income()
    {
        $total_income = Subscription::sum('amount');

        $dailyEarning = Subscription::whereDate('created_at', Carbon::today())
            ->select(DB::raw('SUM(amount) as daily_income'))
            ->first()
            ->daily_income ?? 0;

        // WEEKLY TOTAL INCOME //

        $weeklyTotalSum = Subscription::select(
            DB::raw('(SUM(amount)) as weekly_income')
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->get()
            ->sum('weekly_income');

        // MONTHLY TOTAL INCOME //

        $monthlySumAmount = Subscription::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');

        // YEARLY TOTAL INCOME //

        $yearlySumAmount = Subscription::whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $result = [
            'total_income' => $total_income,
            'daily_income' => $dailyEarning,
            'weekly_income' => $weeklyTotalSum,
            'monthly_income' => $monthlySumAmount,
            'yearly_income' => $yearlySumAmount,
        ];

        return response()->json([
          'data' => $result
        ]);
    }

    public function daily_income(Request $request)
    {
        $packageId = $request->packagId;

        if ($packageId) {
            $transetion = Subscription::where('package_id', $packageId)->whereDate('created_at', Carbon::today())->with('user', 'package')->paginate(10);
        } else {
            $transetion = Subscription::whereDate('created_at', Carbon::today())->with('user', 'package')->paginate(10);
        }
        return response()->json([
            'daily_transection' => $transetion
        ]);
    }

    public function daily_income_details($id)
    {
        $daily_income = Subscription::where('id', $id)->with('user', 'package')->first();
        if ($daily_income) {
            return response()->json([
                'status' => 'success',
                'data' => $daily_income
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => []
            ]);
        }
    }

    public function weekly_income(Request $request)
    {
        $packageId = $request->packageId;

        // Initialize an array to store weekly data
        $weeklyData = [];

        // Start of the current week
        $startOfWeek = Carbon::today();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Iterate through each week starting from the current week and going back in time
        for ($i = 0; $i < 5; $i++) {  // Assuming you want data for the past 5 weeks
            // Calculate start and end of the current week
            $startOfWeek = Carbon::today()->subWeeks($i);  // Use current date as start of the period
            $endOfWeek = Carbon::now()->endOfWeek()->subWeeks($i);

            // Query to get weekly sums
            $query = Subscription::select(
                DB::raw('SUM(amount) as weekly_amount'),
                DB::raw('COUNT(user_id) as total_users')
            )
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek]);

            // If packageId is provided, add package_id condition to the query
            if ($packageId) {
                $query->where('package_id', $packageId);
            }

            // Execute the query
            $weeklySum = $query->first();

            // Store weekly data
            $weeklyData[] = [
                'week_serial' => $i + 1,
                'start_of_week' => $startOfWeek->toDateString(),
                'end_of_week' => $endOfWeek->toDateString(),
                'weekly_amount' => $weeklySum->weekly_amount ?? 0,
                'total_users' => $weeklySum->total_users ?? 0
            ];
        }

        return response()->json($weeklyData);
    }

    public function monthIncome()
    {
        $monthIncom = Subscription::select(
            DB::raw('(SUM(amount)) as count'),
            DB::raw('MONTHNAME(created_at) as month_name'),
            DB::raw('COUNT(user_id) as total_users')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month_name')
            ->get()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'monthly_income' => $monthIncom,
        ]);
    }

    public function monthIncome_ratio(Request $request)
    {
        $monthIncom = Subscription::select(
            DB::raw('(SUM(amount)) as count'),
            DB::raw('MONTHNAME(created_at) as month_name'),
            DB::raw('MONTH(created_at) as month_number')
        )
            ->whereYear('created_at', $request->year)
            ->groupBy('month_name', 'month_number')
            ->orderBy('month_number')
            ->get()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'monthly_income' => $monthIncom,
        ]);
    }
}
