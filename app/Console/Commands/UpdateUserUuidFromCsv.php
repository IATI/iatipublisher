<?php

namespace App\Console\Commands;

use App\IATI\Models\User\User;
use Illuminate\Console\Command;

class UpdateUserUuidFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan users:update-uuids storage/app/decrypted_users.csv
     */
    protected $signature = 'users:update-uuids
                            {file : Path to decrypted_users.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update users.uuid based on decrypted_users.csv (columns: user id, user email)';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        if (($handle = fopen($path, 'r')) === false) {
            $this->error("Unable to open file: {$path}");

            return self::FAILURE;
        }

        $this->info("Processing file: {$path}");

        $header = fgetcsv($handle);

        if ($header === false) {
            $this->error('CSV file appears to be empty.');
            fclose($handle);

            return self::FAILURE;
        }

        // Normalize header names (lowercase + trim) so order/case doesn’t matter
        $normalized = array_map(fn ($h) => strtolower(trim($h)), $header);

        $emailIndex = array_search('user email', $normalized, true);
        $uuidIndex = array_search('user id', $normalized, true);

        if ($emailIndex === false || $uuidIndex === false) {
            $this->error("CSV must contain columns 'user id' and 'user email' (any order, case-insensitive).");
            fclose($handle);

            return self::FAILURE;
        }

        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        $rowNumber = 1; // header row

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Guard against short / malformed rows
            if (!array_key_exists($emailIndex, $row) || !array_key_exists($uuidIndex, $row)) {
                $this->warn("Row {$rowNumber}: missing required columns, skipping");
                $skipped++;

                continue;
            }

            $email = trim($row[$emailIndex] ?? '');
            $uuid = trim($row[$uuidIndex] ?? '');

            if ($email === '' || $uuid === '') {
                $this->warn("Row {$rowNumber}: empty email or uuid, skipping");
                $skipped++;

                continue;
            }

            /** @var User|null $user */
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->warn("Row {$rowNumber}: no user found with email {$email}");
                $notFound++;

                continue;
            }

            if ($user->uuid === $uuid) {
                $skipped++;

                continue;
            }

            $user->uuid = $uuid;
            $user->save();

            $this->line("Updated user {$user->id} ({$email}) -> uuid={$uuid}");
            $updated++;
        }

        fclose($handle);

        $this->info('Done.');
        $this->info("Updated:  {$updated}");
        $this->info("Skipped:  {$skipped}");
        $this->info("Not found: {$notFound}");

        return self::SUCCESS;
    }
}
