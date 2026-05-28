<style>
    @media (max-width: 768px) {
        .md\:flex-row {
            flex-direction: column !important;
        }

        .md\:items-center {
            align-items: flex-start !important;
        }

        .md\:w-40 {
            width: 100% !important;
        }
    }

    details > summary .chevron-up {
        display: inline-block !important;
    }

    details > summary .chevron-down {
        display: none !important;
    }

    details[open] > summary .chevron-up {
        display: none !important;
    }

    details[open] > summary .chevron-down {
        display: inline-block !important;
    }
</style>
