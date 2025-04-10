<?PHP
$common->print_template_card('Magic Search', 'start');
?>

<div class="alert alert-info" role="alert">
    <i class="fa fa-info-circle"></i> Use natural language to describe what you're looking for. Our magic search will do the rest!
</div>

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
    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="useFullText" onchange="if(this.checked) document.getElementById('useChunk').checked = false">
        <label class="form-check-label" for="useFullText">
            Use document full text
        </label>
        <br />
        <input class="form-check-input" type="checkbox" role="switch" id="useChunk" onchange="if(this.checked) document.getElementById('useFullText').checked = false">
        <label class="form-check-label" for="useChunk">
            Use chunk text
        </label>
    </div>
</form>




<div id="searchResults" class="mt-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Search Results</h5>
            <div id="resultContent"></div>
            <button id="copyButton" class="btn btn-secondary mt-3">
                <i class="fa fa-copy"></i> Copy Results
            </button>
        </div>
    </div>
</div>

<div id="fileResults" class="mt-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Matching Files</h5>
            <table id="fileTable" class="table table-striped w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="none">Path</th>
                        <th>AI Title</th>
                        <th class="none">Text</th>
                        <th>Last Found</th>
                    </tr>
                </thead>
                <tbody id="fileTableBody">
                    <!-- File data will be inserted here dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>


<br />
<br />

<h4>Query</h4>

<pre class="line-numbers"><code class="language-sql" id="queryText">
    
</code></pre>


<script>
    function populateFileTable(files) {

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#fileTable')) {
            $('#fileTable').DataTable().destroy();
        }

        const tableBody = document.getElementById('fileTableBody');
        tableBody.innerHTML = ''; // Clear existing content

        files.forEach(file => {
            const row = tableBody.insertRow();
            row.insertCell(0).textContent = file.name;
            row.insertCell(1).textContent = file.path;
            row.insertCell(2).textContent = file.ai_title;
            row.insertCell(3).textContent = file.ai_summary;
            row.insertCell(4).textContent = file.last_found;

            // Add a link to the file name in the first cell
            const nameCell = row.cells[0];
            nameCell.innerHTML = ''; // Clear existing content
            const link = document.createElement('a');
            link.href = `/?s1=File&s2=Detail&id=${file.id}`;
            link.textContent = file.name;
            link.target = "_blank";
            nameCell.appendChild(link);
        });


        // Initialize DataTable
        $('#fileTable').DataTable({
            "paging": true,
            "ordering": true,
            "info": false,
            "searching": true,
            "responsive": true,
            "order": [
                [0, "desc"]
            ],
            "sScrollX": "100%",
        });
    }

    document.getElementById('copyButton').addEventListener('click', function() {
        const resultContent = document.getElementById('resultContent').innerText;
        navigator.clipboard.writeText(resultContent).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Search results have been copied to clipboard.',
                showConfirmButton: false,
                timer: 1500
            });
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    });

    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const query = document.getElementById('searchQuery').value;
        const useFullText = document.getElementById('useFullText').checked;
        const useChunk = document.getElementById('useChunk').checked;

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
                    query: query,
                    useFullText: useFullText,
                    useChunk: useChunk
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                $result = data.result.replace(/\n/g, '<br>');
                document.getElementById('resultContent').innerHTML = `<p>${$result}</p>`;
                populateFileTable(data.files);
                document.getElementById('queryText').innerHTML = data.querytext;
                // Remove empty lines and leading whitespace from query text, all trim each line of text
                document.getElementById('queryText').innerHTML = data.querytext.replace(/^[ \t]*[\r\n]+/gm, '').trim();
                // Trim each line of querytext
                document.getElementById('queryText').innerHTML = data.querytext.split('\n').map(line => line.trim()).join('\n');
                // Initialize Prism syntax highlighting
                Prism.highlightElement(document.getElementById('queryText'));


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