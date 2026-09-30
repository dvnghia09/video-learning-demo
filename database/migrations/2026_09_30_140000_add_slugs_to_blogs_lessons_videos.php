<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['blogs', 'lessons', 'videos'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('slug', 120)->nullable()->unique()->after('title');
            });

            // Tạo slug cho dữ liệu đã có (không trùng nhau)
            $used = [];
            foreach (DB::table($table)->orderBy('id')->get(['id', 'title']) as $row) {
                $base = Str::limit(Str::slug($row->title), 80, '') ?: $table.'-'.$row->id;
                $slug = $base;
                $i = 2;
                while (isset($used[$slug])) {
                    $slug = $base.'-'.$i++;
                }
                $used[$slug] = true;
                DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
            }
        }
    }

    public function down(): void
    {
        foreach (['blogs', 'lessons', 'videos'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['slug']);
                $t->dropColumn('slug');
            });
        }
    }
};
