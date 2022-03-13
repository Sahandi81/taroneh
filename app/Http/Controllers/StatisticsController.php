<?php

namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use App\Models\User;
use Carbon\Carbon;

class StatisticsController extends Controller
{
	# fucking performance
	public function index()
	{
		$usersDetails = [];
		$ordersDetails = [];


		$users 	= User::all();
		$orders = PurchaseHistory::whereNotIn('status', ['unpaid', 'failed'])->orderBy('created_at')->get();

		foreach ($orders->groupBy(function($item) {return $item->created_at->format('Y');}) as $key => $order) {
			$ordersDetails['month'][$key] = $order->count();
		}
		foreach ($orders->groupBy(function($item) {return $item->created_at->format('Y-m-d');}) as $key => $order) {
			$ordersDetails['day'][$key] = $order->count();
		}
		foreach (User::orderBy('created_at')->get()->groupBy(function($item) {return $item->created_at->format('Y');}) as $key => $item) {
			$usersDetails['year'][$key] = $item->count();
		}
		foreach (User::orderBy('created_at')->get()->groupBy(function($item) {return $item->created_at->format('Y-m');}) as $key => $item) {
			$usersDetails['month'][$key] = $item->count();
		}
		foreach (User::orderBy('created_at')->where('created_at', '>', Carbon::now()->modify('-20 days'))->get()->groupBy(function($item) {return $item->created_at->format('Y-m');}) as $key => $item) {
			$usersDetails['day'][$key] = $item->count();
		}

		$users = [
			'all_users'		=> $users->count(),
		];
		$users = array_merge($users,$usersDetails);
		return [
			'users' 	=> $users,
			'orders'	=> $ordersDetails,
		];
    }
}
