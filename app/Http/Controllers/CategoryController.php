<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $incomeCategories  = Category::where('user_id', $user->id)->where('type', 'income')->get(['id','name','type','color']);
        $expenseCategories = Category::where('user_id', $user->id)->where('type', 'expense')->get(['id','name','type','color']);

        return Inertia::render('Categories', [
            'incomeCategories'  => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:income,expense',
            'color' => 'required|string|max:20',
        ]);

        Category::create([
            'user_id' => Auth::id(),
            'name'    => $request->name,
            'type'    => $request->type,
            'color'   => $request->color,
        ]);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        abort_if($category->user_id !== Auth::id(), 403);

        $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:20',
        ]);

        $category->update([
            'name'  => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->user_id !== Auth::id(), 403);
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}