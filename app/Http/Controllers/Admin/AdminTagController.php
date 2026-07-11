<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use Illuminate\Support\Str;

class AdminTagController extends Controller
{
    public function index()
    {
        return TagResource::collection(Tag::all());
    }

    public function store(StoreTagRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        if (Tag::where('slug', $data['slug'])->exists()) {
            return response()->json(['message' => 'Tag slug already exists', 'errors' => ['slug' => ['The slug has already been taken.']]], 422);
        }

        $tag = Tag::create($data);
        return response()->json(new TagResource($tag), 201);
    }

    public function update(UpdateTagRequest $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $data = $request->validated();
        $tag->update($data);
        return response()->json(new TagResource($tag));
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();
        return response()->json(null, 204);
    }
}
