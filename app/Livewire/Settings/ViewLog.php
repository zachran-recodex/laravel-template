<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\File;
use Livewire\Component;

class ViewLog extends Component
{
    public string $logContent = '';
    public int $maxLines = 500;
    public string $searchTerm = '';
    public string $logFile = 'laravel.log';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->readLog();
    }

    /**
     * Read the log file content.
     */
    public function readLog(): void
    {
        $logPath = storage_path('logs/' . $this->logFile);

        if (File::exists($logPath)) {
            // Read the log file content
            $content = File::get($logPath);

            // Split the content into lines and get the last N lines
            $lines = explode("\n", $content);
            $lines = array_filter($lines); // Remove empty lines

            // Apply search filter if search term is provided
            if (!empty($this->searchTerm)) {
                $lines = array_filter($lines, function ($line) {
                    return stripos($line, $this->searchTerm) !== false;
                });
            }

            // Get the last N lines
            $lines = array_slice($lines, -$this->maxLines);

            // Join the lines back together
            $this->logContent = implode("\n", $lines);
        } else {
            $this->logContent = 'Log file not found.';
        }
    }

    /**
     * Clear the log file.
     */
    public function clearLog(): void
    {
        $logPath = storage_path('logs/' . $this->logFile);

        if (File::exists($logPath)) {
            File::put($logPath, '');
            $this->logContent = 'Log file cleared.';
        }
    }

    /**
     * Update the search results when search term changes.
     */
    public function updatedSearchTerm(): void
    {
        $this->readLog();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.settings.view-log');
    }
}
