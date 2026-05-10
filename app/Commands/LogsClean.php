<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ActivityLogModel;

class LogsClean extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Maintenance';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'logs:clean';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Deletes activity logs older than 30 days to optimize the database.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'logs:clean [days]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'days' => 'Retention period in days (default: 30)',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $days = $params[0] ?? 30;
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        CLI::write("Cleaning logs older than {$days} days ({$date})...", 'yellow');

        $logModel = new ActivityLogModel();
        
        // Count before deletion
        $count = $logModel->where('created_at <', $date)->countAllResults();

        if ($count === 0) {
            CLI::write("No logs found to delete.", 'green');
            return;
        }

        if (CLI::prompt("Delete {$count} logs permanently?", ['y', 'n']) === 'y') {
            $logModel->where('created_at <', $date)->delete();
            CLI::write("Optimization complete! {$count} logs removed.", 'green');
        } else {
            CLI::write("Operation cancelled.", 'red');
        }
    }
}
