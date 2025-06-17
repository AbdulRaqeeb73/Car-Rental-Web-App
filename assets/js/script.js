// Function to calculate rental cost
function calculateRentalCost() {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    const pricePerDay = parseFloat(document.getElementById('price_per_day').value);
    const totalCostElement = document.getElementById('total_cost');
    const totalDaysElement = document.getElementById('total_days');
    
    // Validate dates
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        totalCostElement.textContent = '0.00';
        totalDaysElement.textContent = '0';
        return;
    }
    
    if (endDate < startDate) {
        alert('End date must be after start date!');
        document.getElementById('end_date').value = '';
        totalCostElement.textContent = '0.00';
        totalDaysElement.textContent = '0';
        return;
    }
    
    // Calculate difference in days
    const diffTime = Math.abs(endDate - startDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    // Calculate total cost
    const totalCost = diffDays * pricePerDay;
    
    // Update UI
    totalDaysElement.textContent = diffDays;
    totalCostElement.textContent = totalCost.toFixed(2);
    
    // Update hidden field for form submission
    document.getElementById('total_cost_input').value = totalCost.toFixed(2);
}

// Set minimum date for date inputs to today
function setMinDates() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        input.min = today;
    });
}

// Initialize date inputs when page loads
document.addEventListener('DOMContentLoaded', function() {
    setMinDates();
    
    // Set up event listeners for rental date inputs
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            // Set min value of end_date to be at least the start_date
            endDateInput.min = startDateInput.value;
            calculateRentalCost();
        });
        
        endDateInput.addEventListener('change', calculateRentalCost);
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('invalid');
                } else {
                    field.classList.remove('invalid');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields!');
            }
        });
    });
}); 