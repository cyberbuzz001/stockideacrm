<?php

namespace App\Traits;

use Carbon\Carbon;

trait ShardsByYear
{
    /**
     * Set the specific year for the shard.
     */
    protected $shardYear = null;

    /**
     * Statically set the year for a query instance.
     * Usage: Lead::year(2025)->get();
     */
    public static function year($year)
    {
        $instance = new static;
        $instance->setShardYear($year);
        return $instance->newQuery();
    }

    /**
     * Get the table associated with the model.
     * Determines the current shard based on the set year or current year.
     */
    public function getTable()
    {
        $baseTable = parent::getTable();
        
        // If it's the base 'leads' name, let's append the year. 
        // We only do this if we haven't already appended it to prevent recursion issues.
        if (preg_match('/_[0-9]{4}$/', $baseTable)) {
            return $baseTable;
        }

        $year = $this->shardYear ?: Carbon::now()->year;
        
        // Only shard leads for 2025 onwards as per plan, older legacy might stay in 'leads'
        $shardedTable = "{$baseTable}_{$year}";

        return \Illuminate\Support\Facades\Schema::hasTable($shardedTable) 
               ? $shardedTable 
               : $baseTable; // Fallback to base table if shard doesn't exist yet
    }

    /**
     * Set the shard year on the instance.
     */
    public function setShardYear($year)
    {
        $this->shardYear = $year;
        $this->setTable($this->getTable());
        return $this;
    }
}
