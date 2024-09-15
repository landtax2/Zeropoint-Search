<?PHP
$common->print_template_card('Classification Prompts', 'start');

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/file_classification/ai_processing.class.php';
$ai_processing = new ai_processing($common);
$sample_text = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/misc/sample_text.txt');

?>

<style>
    /* Not needed for this page */
    #user_data_div {
        display: none;
    }
</style>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">File Classification Prompt Testing</h5>
                <p class="card-text">
                    This page allows you to test and refine the prompts used for automatic file classification. You can evaluate how effectively the system categorizes and tags files based on their content.
                </p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Test various classification prompts</li>
                    <li class="list-group-item">Evaluate prompt effectiveness</li>
                    <li class="list-group-item">Refine prompts for better accuracy</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="accordion" id="classificationPromptsAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="piiPromptHeading">
            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse" data-coreui-target="#piiPromptCollapse" aria-expanded="false" aria-controls="piiPromptCollapse">
                PII Prompt
            </button>
        </h2>
        <div id="piiPromptCollapse" class="accordion-collapse collapse" aria-labelledby="piiPromptHeading" data-coreui-parent="#classificationPromptsAccordion">
            <div class="d-none">
                <textarea id="pii_prompt_text" class="form-control"><?php echo $ai_processing->get_pii_prompt($sample_text); ?></textarea>
            </div>
            <div class="accordion-body">
                <div class="mb-3">
                    <h5>Prompt:</h5>
                    <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($ai_processing->get_pii_prompt('[extracted_text]')); ?></code></pre>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#ai_chat_modal" onclick="open_chat(document.getElementById('pii_prompt_text').value, '')">
                        <i class="cil-media-play mr-2"></i>Test Prompt with Sample Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="titlePromptHeading">
            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse" data-coreui-target="#titlePromptCollapse" aria-expanded="false" aria-controls="titlePromptCollapse">
                Title Prompt
            </button>
        </h2>
        <div id="titlePromptCollapse" class="accordion-collapse collapse" aria-labelledby="titlePromptHeading" data-coreui-parent="#classificationPromptsAccordion">
            <div class="d-none">
                <textarea id="title_prompt_text" class="form-control"><?php echo $ai_processing->get_title_prompt($sample_text); ?></textarea>
            </div>
            <div class="accordion-body">
                <div class="mb-3">
                    <h5>Prompt:</h5>
                    <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($ai_processing->get_title_prompt('[extracted_text]')); ?></code></pre>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#ai_chat_modal" onclick="open_chat(document.getElementById('title_prompt_text').value, '')">
                        <i class="cil-media-play mr-2"></i>Test Prompt with Sample Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="summaryPromptHeading">
            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse" data-coreui-target="#summaryPromptCollapse" aria-expanded="false" aria-controls="summaryPromptCollapse">
                Summary Prompt
            </button>
        </h2>
        <div id="summaryPromptCollapse" class="accordion-collapse collapse" aria-labelledby="summaryPromptHeading" data-coreui-parent="#classificationPromptsAccordion">
            <div class="d-none">
                <textarea id="summary_prompt_text" class="form-control"><?php echo $ai_processing->get_summary_prompt($sample_text); ?></textarea>
            </div>
            <div class="accordion-body">
                <div class="mb-3">
                    <h5>Prompt:</h5>
                    <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($ai_processing->get_summary_prompt('[extracted_text]')); ?></code></pre>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#ai_chat_modal" onclick="open_chat(document.getElementById('summary_prompt_text').value, '')">
                        <i class="cil-media-play mr-2"></i>Test Prompt with Sample Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header" id="tagPromptHeading">
            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse" data-coreui-target="#tagPromptCollapse" aria-expanded="false" aria-controls="tagPromptCollapse">
                Tag Prompt
            </button>
        </h2>
        <div id="tagPromptCollapse" class="accordion-collapse collapse" aria-labelledby="tagPromptHeading" data-coreui-parent="#classificationPromptsAccordion">
            <div class="d-none">
                <textarea id="tag_prompt_text" class="form-control"><?php echo $ai_processing->get_ai_tags_prompt($sample_text); ?></textarea>
            </div>
            <div class="accordion-body">
                <div class="mb-3">
                    <h5>Prompt:</h5>
                    <pre class="bg-light p-3 rounded"><code><?php echo htmlspecialchars($ai_processing->get_ai_tags_prompt('[extracted_text]')); ?></code></pre>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" data-coreui-toggle="modal" data-coreui-target="#ai_chat_modal" onclick="open_chat(document.getElementById('tag_prompt_text').value, '')">
                        <i class="cil-media-play mr-2"></i>Test Prompt with Sample Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<?PHP
$common->print_template_card(null, 'end');
?>