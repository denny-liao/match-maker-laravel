<?php

namespace Src\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:controller {boundedContext} {domainName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new controller in the specified domain';

    /**
     * Execute the console command.
     */
     public function handle(): int
    {
        $boundedContext = Str::studly($this->argument('boundedContext'));
        $domainName = Str::studly($this->argument('domainName'));

        // 使用 base_path() 確保路徑絕對正確
        $path = base_path("src/{$boundedContext}/{$domainName}/Presentation/Controllers/{$domainName}Controller.php");
        $stubPath = base_path('stubs/Controller.stub');

        // 1. 檢查 Stub 是否存在
        if (! File::exists($stubPath)) {
            $this->error("找不到樣板檔案：{$stubPath}");
            return self::FAILURE;
        }

        // 2. 防止意外覆蓋現有程式碼
        if (File::exists($path) && ! $this->option('force')) {
            $this->error("檔案已存在：{$path}（若要覆蓋請加上 --force）");
            return self::FAILURE;
        }

        // 3. 自動建立多層資料夾
        File::ensureDirectoryExists(dirname($path));

        $stub = File::get($stubPath);
        $content = strtr($stub, [
            '**BoundedContext**' => $boundedContext,
            '**Domain**' => $domainName,
            '**domain_lc**' => Str::snake($domainName),
        ]);

        File::put($path, $content);

        $this->info("成功建立 Controller：{$path}");

        // 4. 回傳正確的成功狀態碼 (0)
        return self::SUCCESS;
    }
}
