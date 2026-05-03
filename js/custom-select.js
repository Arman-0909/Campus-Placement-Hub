document.addEventListener('DOMContentLoaded', function () {
    // Find all select elements with 'form-select' class
    const selects = document.querySelectorAll('.form-select');

    selects.forEach(select => {
        // Skip if already initialized
        if (select.nextElementSibling && select.nextElementSibling.classList.contains('custom-select-wrapper')) {
            return;
        }

        // Create the wrapper
        const wrapper = document.createElement('div');
        wrapper.classList.add('custom-select-wrapper');

        // Create the trigger (displayed value)
        const trigger = document.createElement('div');
        trigger.classList.add('custom-select-trigger');
        const selectedOption = select.options[select.selectedIndex];

        // SVG Arrow
        const arrowSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="custom-arrow-icon"><path d="m6 9 6 6 6-6"/></svg>`;

        // Handle placeholder
        let displayText = 'Select';
        if (selectedOption && selectedOption.value !== "") {
            displayText = selectedOption.text;
        } else if (selectedOption && selectedOption.value === "") {
            // If "All Companies" is selected, show that, or show Placeholder text if text is empty?
            // Usually option value="" has text "All Companies"
            displayText = selectedOption.text || 'Select';
        }

        trigger.innerHTML = `<span>${displayText}</span>${arrowSvg}`;

        // Create the options list
        const optionsList = document.createElement('div');
        optionsList.classList.add('custom-options');

        // Loop through real options and create custom ones
        Array.from(select.options).forEach(option => {
            // Keep empty value options (like "All Companies")

            const customOption = document.createElement('div');
            customOption.classList.add('custom-option');
            customOption.dataset.value = option.value;
            customOption.textContent = option.text;

            if (option.selected) {
                customOption.classList.add('selected');
            }

            if (option.value === "") {
                customOption.classList.add('option-all');
            }

            // Handle Option Click
            customOption.addEventListener('click', function (e) {
                e.stopPropagation();

                // Update Trigger Text
                trigger.querySelector('span').textContent = this.textContent;

                // Update Original Select Value
                select.value = this.dataset.value;

                // Update Visual Selection
                optionsList.querySelectorAll('.custom-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                // Close Dropdown
                wrapper.classList.remove('open');

                // Trigger Change Event on Original Select (for forms)
                const event = new Event('change');
                select.dispatchEvent(event);
            });

            optionsList.appendChild(customOption);
        });

        wrapper.appendChild(optionsList);
        wrapper.appendChild(trigger); // Trigger should constitute the box, list appended? 
        // Logic check: trigger is the box. list should be sibling or child. 
        // Original code: wrapper.appendChild(trigger); wrapper.appendChild(optionsList);
        // My previous write had incorrect order maybe? No, typically list is after trigger.

        // Insert Wrapper after original select and hide original
        select.parentNode.insertBefore(wrapper, select.nextSibling);
        select.style.display = 'none'; // Hide original

        // Correct DOM Order for absolute positioning
        wrapper.innerHTML = '';
        wrapper.appendChild(trigger);
        wrapper.appendChild(optionsList);


        // Toggle Open/Close
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            // Close other open dropdowns
            document.querySelectorAll('.custom-select-wrapper.open').forEach(opened => {
                if (opened !== wrapper) opened.classList.remove('open');
            });
            wrapper.classList.toggle('open');
        });
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.custom-select-wrapper.open').forEach(wrapper => {
            if (!wrapper.contains(e.target)) {
                wrapper.classList.remove('open');
            }
        });
    });
});
