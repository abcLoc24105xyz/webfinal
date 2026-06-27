<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Movie;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    const STATUS_COMING_SOON = 1;
    const STATUS_SHOWING = 2;
    const STATUS_ENDED = 3;

    /**
     * Danh sách phim
     */
    public function index(Request $request)
    {
        $query = Movie::with('category');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('cate_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('release_date')) {
            $query->whereDate('release_date', $request->release_date);
        }

        $movies = $query
            ->latest('movie_id')
            ->paginate(10);

        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Form thêm phim
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.movies.create', compact('categories'));
    }

    /**
     * Lưu phim
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'title' => 'required|max:255|unique:movies,title',

            'cate_id' => 'required|exists:categories,cate_id',

            'director' => 'required|max:150',

            'duration' => 'required|integer|min:1|max:300',

            'release_date' => 'required|date',

            'early_premiere_date' => 'nullable|date|before:release_date',

            'age_limit' => 'required|in:0,13,16,18',

            'poster' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'trailer' => 'nullable|url',

            'description' => 'required|max:2000',

        ]);

        DB::beginTransaction();

        try {

            //----------------------------------
            // Upload poster
            //----------------------------------

            $posterName = null;

            if ($request->hasFile('poster')) {

                $folder = public_path('poster');

                if (!file_exists($folder)) {
                    mkdir($folder, 0755, true);
                }

                $posterName = time() . '_' . $request->poster->getClientOriginalName();

                $request->poster->move($folder, $posterName);
            }

            //----------------------------------
            // Status
            //----------------------------------

            $today = Carbon::today();

            $startDate = $validated['early_premiere_date']
                ?? $validated['release_date'];

            $status = Carbon::parse($startDate)->lte($today)
                ? self::STATUS_SHOWING
                : self::STATUS_COMING_SOON;

            //----------------------------------
            // Insert
            //----------------------------------

            $movie = Movie::create([

                'title' => $validated['title'],

                'slug' => '',

                'cate_id' => $validated['cate_id'],

                'director' => $validated['director'],

                'duration' => $validated['duration'],

                'description' => $validated['description'],

                'release_date' => $validated['release_date'],

                'early_premiere_date' => $validated['early_premiere_date'],

                'poster' => $posterName,

                'trailer' => $validated['trailer'] ?? null,

                'age_limit' => $validated['age_limit'],

                'status' => $status,

                'created_at' => now(),

            ]);

            //----------------------------------
            // Sinh slug sau khi có movie_id
            //----------------------------------

            $slug = Str::slug($movie->title) . '-' . $movie->movie_id;

            $movie->update([
                'slug' => $slug
            ]);

            DB::commit();

            return redirect()
                ->route('admin.movies.index')
                ->with('success', 'Thêm phim thành công.');
        }

        catch (Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

        /**
     * Form sửa phim
     */
    public function edit($id)
    {
        $movie = Movie::findOrFail($id);

        $categories = Category::orderBy('name')->get();

        return view('admin.movies.edit', compact('movie', 'categories'));
    }

    /**
     * Cập nhật phim
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validated = $request->validate([

            'title' => 'required|max:255|unique:movies,title,' . $movie->movie_id . ',movie_id',

            'cate_id' => 'required|exists:categories,cate_id',

            'director' => 'required|max:150',

            'duration' => 'required|integer|min:1|max:300',

            'release_date' => 'required|date',

            'early_premiere_date' => 'nullable|date|before:release_date',

            'age_limit' => 'required|in:0,13,16,18',

            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'trailer' => 'nullable|url',

            'description' => 'required|max:2000',

        ]);

        DB::beginTransaction();

        try {

            $data = [

                'title' => $validated['title'],

                'cate_id' => $validated['cate_id'],

                'director' => $validated['director'],

                'duration' => $validated['duration'],

                'description' => $validated['description'],

                'release_date' => $validated['release_date'],

                'early_premiere_date' => $validated['early_premiere_date'],

                'trailer' => $validated['trailer'] ?? null,

                'age_limit' => $validated['age_limit'],

            ];

            /**
             * Upload poster mới
             */
            if ($request->hasFile('poster')) {

                $folder = public_path('poster');

                if (!file_exists($folder)) {
                    mkdir($folder, 0755, true);
                }

                // xóa poster cũ
                if (
                    $movie->poster &&
                    file_exists($folder . '/' . $movie->poster)
                ) {
                    @unlink($folder . '/' . $movie->poster);
                }

                $posterName = time() . '_' . $request->poster->getClientOriginalName();

                $request->poster->move($folder, $posterName);

                $data['poster'] = $posterName;
            }

            /**
             * Sinh lại slug
             */
            $data['slug'] = Str::slug($validated['title']) . '-' . $movie->movie_id;

            /**
             * Tự cập nhật status
             */

            $today = Carbon::today();

            $startDate = $validated['early_premiere_date']
                ?? $validated['release_date'];

            $endDate = Carbon::parse($startDate)
                ->copy()
                ->addWeeks(4);

            if ($today->gt($endDate)) {

                $data['status'] = self::STATUS_ENDED;

            } elseif ($today->gte(Carbon::parse($startDate))) {

                $data['status'] = self::STATUS_SHOWING;

            } else {

                $data['status'] = self::STATUS_COMING_SOON;

            }

            $movie->update($data);

            DB::commit();

            return redirect()
                ->route('admin.movies.index')
                ->with('success', 'Cập nhật phim thành công.');

        } catch (Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa phim
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);

        if ($movie->shows()->exists()) {
            return back()->with('error', 'Không thể xóa phim đã có suất chiếu.');
        }

        DB::beginTransaction();

        try {

            if ($movie->poster) {

                $poster = public_path('poster/' . $movie->poster);

                if (file_exists($poster)) {
                    @unlink($poster);
                }

            }

            $movie->delete();

            DB::commit();

            return redirect()
                ->route('admin.movies.index')
                ->with('success', 'Xóa phim thành công.');

        } catch (Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());

        }
    }

    /**
     * Đổi trạng thái phim
     */
    public function toggleStatus($id)
    {
        $movie = Movie::findOrFail($id);

        $today = Carbon::today();

        $startDate = $movie->early_premiere_date
            ?? $movie->release_date;

        $endDate = Carbon::parse($startDate)
            ->copy()
            ->addWeeks(4);

        if ($today->gt($endDate)) {

            $status = self::STATUS_ENDED;
            $label = 'Kết thúc';

        } elseif ($today->gte(Carbon::parse($startDate))) {

            $status = self::STATUS_SHOWING;
            $label = 'Đang chiếu';

        } else {

            $status = self::STATUS_COMING_SOON;
            $label = 'Sắp chiếu';

        }

        $movie->update([
            'status' => $status
        ]);

        return back()->with('success', "Đã cập nhật trạng thái: {$label}");
    }

    /**
     * API lấy thông tin phim
     */
    public function show($id)
    {
        $movie = Movie::findOrFail($id);

        return response()->json([

            'movie_id' => $movie->movie_id,

            'title' => $movie->title,

            'duration' => $movie->duration,

            'release_date' => optional($movie->release_date)
                ->format('Y-m-d'),

            'early_premiere_date' => optional($movie->early_premiere_date)
                ->format('Y-m-d'),

        ]);
    }

    /**
     * Cập nhật trạng thái tất cả phim
     */
    public function updateAllStatus()
    {
        $movies = Movie::all();

        $count = 0;

        foreach ($movies as $movie) {

            $today = Carbon::today();

            $startDate = $movie->early_premiere_date
                ?? $movie->release_date;

            $endDate = Carbon::parse($startDate)
                ->copy()
                ->addWeeks(4);

            if ($today->gt($endDate)) {

                $status = self::STATUS_ENDED;

            } elseif ($today->gte(Carbon::parse($startDate))) {

                $status = self::STATUS_SHOWING;

            } else {

                $status = self::STATUS_COMING_SOON;

            }

            if ($movie->status != $status) {

                $movie->update([
                    'status' => $status
                ]);

                $count++;

            }
        }

        return "Đã cập nhật {$count} phim.";
    }
}