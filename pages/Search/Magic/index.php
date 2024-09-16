<?PHP
$common->print_template_card('Magic Search', 'start');
?>

<form id="searchForm" class="mb-4">
    <div class="form-group mb-3">
        <label for="searchQuery" class="form-label">Enter your search query:</label>
        <div class="input-group">
            <input type="text" class="form-control" id="searchQuery" placeholder="Type your query here..." required>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-search"></i> Search
            </button>
        </div>
    </div>
</form>

<div class="alert alert-info" role="alert">
    <i class="fa fa-info-circle"></i> Use natural language to describe what you're looking for. Our magic search will do the rest!
</div>

<div id="searchResults" class="mt-4"></div>

<script>
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const query = document.getElementById('searchQuery').value;

        // Show loading screen
        Swal.fire({
            title: 'Searching...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Perform API request
        fetch('/application_api/search/index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'magic_search',
                    query: query
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                document.getElementById('searchResults').innerHTML = `<p>${data.result}</p>`;
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong with the search. Please try again.',
                });
                console.error('Error:', error);
            });
    });
</script>

<?PHP
$common->print_template_card(null, 'end');
?>