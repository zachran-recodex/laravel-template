<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class ArtisanCommand extends Component
{
    public string $command = '';
    public string $output = '';
    public bool $isRunning = false;

    /**
     * Run the artisan command.
     */
    public function runCommand(): void
    {
        $this->isRunning = true;
        $this->output = '';

        // Get the command (no need to check for 'php artisan' prefix as it's in the UI)
        $command = trim($this->command);

        // Check if the command is empty
        if (empty($command)) {
            $this->output = "Error: Please specify an artisan command";
            $this->isRunning = false;
            return;
        }

        // Check if the command is empty
        if (empty($command)) {
            $this->output = "Error: Please specify an artisan command";
            $this->isRunning = false;
            return;
        }

        // Run the command and capture output
        try {
            // Start output buffering
            ob_start();

            // Run the command
            $exitCode = Artisan::call($command);

            // Get the output
            $output = Artisan::output();

            // End output buffering
            ob_end_clean();

            // Set the output
            $this->output = "Command executed with exit code: {$exitCode}\n\n{$output}";
        } catch (\Exception $e) {
            $this->output = "Error: {$e->getMessage()}";
        }

        $this->isRunning = false;
    }

    /**
     * Clear the command and output.
     */
    public function clearOutput(): void
    {
        $this->output = '';
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.settings.artisan-command');
    }
}
