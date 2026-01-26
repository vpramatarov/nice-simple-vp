document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.filter-collections .btn');
    const projectCards = document.querySelectorAll('.project-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // 1. Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // 2. Add active class to clicked button
            button.classList.add('active');

            // 3. Get value to filter by
            const filterValue = button.getAttribute('data-filter');

            projectCards.forEach(card => {
                // Get the categories string (e.g., "офис, кухни") and split into an array
                const cardCategories = card.getAttribute('data-category').split(', ');

                // Check if "all" is selected OR if the card's categories include the filter value
                if (filterValue === 'all' || cardCategories.includes(filterValue)) {
                    card.classList.remove('hide');

                    // Reset animation
                    card.style.animation = 'none';
                    card.offsetHeight; /* Trigger reflow */
                    card.style.animation = 'fadeIn 0.5s ease-in-out';
                } else {
                    card.classList.add('hide');
                }
            });
        });
    });
});