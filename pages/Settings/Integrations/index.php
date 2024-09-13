<?PHP
$common->print_template_card('Integrations', 'start');
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const testDoctorButton = document.getElementById('test-doctor-button');
        if (testDoctorButton) {
            testDoctorButton.addEventListener('click', function() {
                Swal.fire({
                    title: 'Testing Doctor Integration',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('/application_api/settings/index.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'integration_doctor_test'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Display the test results
                            const testResultsDiv = document.getElementById('doctor-test-results');
                            const testOutputPre = document.getElementById('doctor-test-output');
                            testOutputPre.textContent = data.data.extracted_text;
                            testResultsDiv.style.display = 'block';
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Doctor integration test successful!'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Doctor integration test failed: ' + data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while testing the Doctor integration.'
                        });
                    });
            });
        }

        const testStirlingButton = document.getElementById('test-stirling-button');
        if (testStirlingButton) {
            testStirlingButton.addEventListener('click', function() {
                Swal.fire({
                    title: 'Testing Stirling PDF Integration',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                fetch('/application_api/settings/index.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'test_stirling_pdf'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Display the test results
                            const testResultsDiv = document.getElementById('stirling-test-results');
                            const testOutputPdf = document.getElementById('stirling-test-pdf');
                            testOutputPdf.src = 'data:application/pdf;base64,' + data.file_data;
                            testResultsDiv.style.display = 'block';
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Stirling PDF integration test successful!'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Stirling PDF integration test failed: ' + data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while testing the Stirling PDF integration.'
                        });
                    });
            });
        }
    });
</script>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Third-Party API Integration Tests</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    This section is dedicated to testing API calls to various third-party components that are essential for the proper functioning of our application. These tests ensure that our integrations are working correctly and help identify any potential issues or connectivity problems.
                </p>
                <p class="card-text">
                    By running these tests, we can verify:
                </p>
                <ul>
                    <li>The connection to each third-party service is established successfully</li>
                    <li>Our API keys and authentication methods are valid and up-to-date</li>
                    <li>The responses from these services are in the expected format and contain the necessary data</li>
                    <li>Any errors or exceptions are properly handled and reported</li>
                </ul>
            </div>
        </div>
    </div>
</div>


<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Doctor Integration Test</h4>
            </div>
            <div class="card-body">
                <p class="mb-3">This will test PDF document extraction from <a href="?s1=Docs&s2=Doctor">Doctor</a>.<br /> The current Doctor endpoint is <?PHP echo $common->get_config_value('DOCTOR_API'); ?></p>
                <button id="test-doctor-button" class="btn btn-primary">
                    <i class="fa fa-vial me-2"></i>Test Doctor Integration
                </button>
                <div id="doctor-test-results" class="mt-4" style="display: none;">
                    <h5>Doctor Test Results</h5>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Extracted Text</h6>
                        </div>
                        <div class="card-body">
                            <pre id="doctor-test-output" class="mb-0 p-3" style="white-space: pre-wrap; word-break: break-word; max-height: 300px; overflow-y: auto;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Ollama Integration Test</h4>
            </div>
            <div class="card-body">
                <p class="mb-3">This will test the <a href="?s1=Docs&s2=Ollama">Ollama</a> AI LLM API used for document processing.<br />The current Ollama endpoint is <?PHP echo $common->get_config_value('CHAT_API_OLLAMA'); ?></p>
                <button id="test-ollama-button" class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#ai_chat_modal" onclick="open_chat('Is this thing working?', '')">
                    <i class="fa fa-brain me-2"></i>Test Ollama Integration
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Stirling PDF Integration Test</h4>
            </div>
            <div class="card-body">
                <p class="mb-3">This will test the <a href="?s1=Docs&s2=StirlingPDF">StirlingPDF</a> API used to convert documents such as PPTX to PDF.<br />The current StirlingPDF endpoint is <?PHP echo $common->get_config_value('STIRLING_PDF_API'); ?></p>
                <button id="test-stirling-button" class="btn btn-primary">
                    <i class="fa fa-file-pdf me-2"></i>Test Stirling Integration
                </button>
                <div id="stirling-test-results" class="mt-4" style="display: none;">
                    <h5>Stirling PDF Test Results</h5>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">PDF File</h6>
                        </div>
                        <div class="card-body">
                            <embed type="application/pdf" id="stirling-test-pdf" width="100%" height="600px" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
$common->print_template_card('Database Settings', 'end');
?>