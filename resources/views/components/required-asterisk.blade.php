
<!-- resources/views/components/required-asterisk.blade.php -->

<div class="relative inline-block">
    <div class="tooltip-container">
        <b class="hover-element">*</b>
        <span class="tooltiptext">Required Value</span>
    </div>
</div>

<style>
    .tooltip-container {
        position: relative;
        padding-right: 1ch;
        padding-left: 1ch;
    }

    .hover-element {
        cursor: pointer;
        color: red;
        display: inline-block;
        padding-right: 1ch;
    }

    .tooltiptext {
        visibility: hidden;
        background-color: #555;
        color: #fff;
        border-radius: 6px;
        padding: 5px 0;
        z-index: 1;
        bottom: 125%;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .hover-element:hover ~ .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
</style>