<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SortableColumn extends Component
{
    public $column;
    public $label;
    public $sort;
    public $direction;
    public $nextDirection;

    public function __construct($column, $label, $sort, $direction)
    {
        $this->column = $column; // The column name
        $this->label = $label; // The display label for the column
        $this->sort = $sort; // The currently sorted column
        $this->direction = $direction; // The current sort direction
        $this->nextDirection = $this->calculateNextDirection(); // Pre-compute next direction
    }

    private function calculateNextDirection()
    {
        // Toggle the direction
        return $this->sort === $this->column && $this->direction === 'asc' ? 'desc' : 'asc';
    }

    public function render()
    {
        return view('components.sortable-column');
    }
}
