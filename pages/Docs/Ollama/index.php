<?PHP
$common->print_template_card('Ollama API Endpoint', 'start');
?>

<style>
    /* Not needed for this page */
    #user_data_div {
        display: none;
    }
</style>


<h2>Setting up Ollama on Windows</h2>
<h3>Understanding the Ollama API Endpoint</h3>

<p>The Ollama API endpoint serves as the primary interface to the Llama AI Large Language Model (LLM). This endpoint allows our application to communicate with and leverage the powerful capabilities of the Llama model.</p>
<p>Ollama is an AI model server that allows you to run large language models locally. This guide will walk you through the process of setting up Ollama on a Windows system.</p>
<h3>System Requirements</h3>
<p>While Ollama can run on most modern Windows systems, for optimal performance, the following is recommended:</p>
<ul>
    <li>A recent version of Windows 10 or Windows 11</li>
    <li>At least 8GB of RAM</li>
    <li>An NVIDIA GPU with at least 8GB of VRAM is recommended, but not necessary</li>
</ul>
<p>Note that while a powerful GPU can significantly speed up model inference, Ollama can still function on systems without a dedicated GPU, albeit at a slower pace.</p>

<h3>Step 1: Download Ollama</h3>
<ol>
    <li>Visit the official Ollama website: <a href="https://ollama.ai/download" target="_blank">https://ollama.ai/download</a></li>
    <li>Click on the "Download for Windows" button to download the installer.</li>
</ol>

<h3>Step 2: Install Ollama</h3>
<ol>
    <li>Locate the downloaded installer file (usually in your Downloads folder).</li>
    <li>Double-click the installer file to start the installation process.</li>
    <li>If prompted by Windows security, click "Run" or "Yes" to allow the installation.</li>
    <li>Follow the on-screen instructions in the installer wizard.</li>
    <li>Choose the installation directory (the default is usually fine).</li>
    <li>Wait for the installation to complete.</li>
</ol>

<h3>Step 3: Launch Ollama</h3>
<ol>
    <li>After installation, Ollama should start automatically.</li>
    <li>If it doesn't start automatically, you can launch it manually:
        <ul>
            <li>Search for "Ollama" in the Windows start menu.</li>
            <li>Click on the Ollama icon to start the application.</li>
        </ul>
    </li>
    <li>When Ollama starts, it will open a command prompt window. This window must remain open for Ollama to function.</li>
</ol>

<h3>Step 4: Verify Installation</h3>
<ol>
    <li>Open a new command prompt or PowerShell window.</li>
    <li>Type the following command and press Enter:
        <pre><code class="language-powershell">ollama run llama3.1</code></pre>
    </li>
    <li>If Ollama is installed correctly, it will download the Llama 3.1 model (if not already present) and start a chat session.</li>
</ol>


<h3>Step 5: Open Firewall Port</h3>
<p>By default, Ollama uses port 11434. You need to ensure this port is open in your Windows Firewall to allow the application to communicate with Ollama.</p>

<ol>
    <li>Open the Windows Start menu and search for "Windows Defender Firewall with Advanced Security". Click to open it.</li>
    <li>In the left pane, click on "Inbound Rules".</li>
    <li>In the right pane, click on "New Rule...".</li>
    <li>Select "Port" and click "Next".</li>
    <li>Choose "TCP" and enter "11434" as the specific local port. Click "Next".</li>
    <li>Select "Allow the connection" and click "Next".</li>
    <li>Ensure all profile boxes (Domain, Private, Public) are checked and click "Next".</li>
    <li>Give the rule a name (e.g., "Ollama API") and click "Finish".</li>
</ol>

<p>After completing these steps, the firewall should allow incoming connections to Ollama on port 11434.</p>


<h3>Step 6: Set OLLAMA_HOST Environment Variable</h3>
<p>To allow Ollama to communicate outside of localhost, you need to set the OLLAMA_HOST environment variable to 0.0.0.0. This step is crucial for enabling the application to interact with Ollama from other machines on your network.</p>

<ol>
    <li>Open the Windows Start menu and search for "Environment Variables". Click on "Edit the system environment variables".</li>
    <li>In the System Properties window, click the "Environment Variables" button.</li>
    <li>In the "System variables" section, click "New...".</li>
    <li>For "Variable name", enter:
        <pre><code class="language-plaintext">OLLAMA_HOST</code></pre>
    </li>
    <li>For "Variable value", enter:
        <pre><code class="language-plaintext">0.0.0.0</code></pre>
    </li>
    <li>Click "OK" to close each window.</li>
    <li>Restart your computer for the changes to take effect.</li>
    <li>After restarting, login and OLLAMA should be running on port 11434</li>
</ol>

<h3>Step 7: Setup an uncensored model</h3>
<p>By default, Ollama will not allow you to use the uncensored model. You need to setup a new model file to allow it.</p>
<ol>
    <li>Download the Abiltered llama 3.1 model from <a href="https://huggingface.co/mlabonne/Meta-Llama-3.1-8B-Instruct-abliterated-GGUF/tree/main" target="_blank">here</a></li>
    <li>The recommended model is the Q4_K_M model</li>
    <li>Create a model card for the model with the name "uncensored" in the same folder as the model</li>
    <li>Add the following to the model card file</li>
    <pre><code class="language-plaintext">
       
FROM C:\AI\ollama\models\llama3_1_abilterated\meta-llama-3.1-8b-instruct-abliterated.Q8_0.gguf
TEMPLATE """{{- if or .System .Tools }}<|start_header_id|>system<|end_header_id|>
{{- if .System }}

{{ .System }}
{{- end }}
{{- if .Tools }}

Cutting Knowledge Date: December 2023

When you receive a tool call response, use the output to format an answer to the orginal user question.

You are a helpful assistant with tool calling capabilities.
{{- end }}
{{- end }}<|eot_id|>
{{- range $i, $_ := .Messages }}
{{- $last := eq (len (slice $.Messages $i)) 1 }}
{{- if eq .Role "user" }}<|start_header_id|>user<|end_header_id|>
{{- if and $.Tools $last }}

Given the following functions, please respond with a JSON for a function call with its proper arguments that best answers the given prompt.

Respond in the format {"name": function name, "parameters": dictionary of argument name and its value}. Do not use variables.

{{ range $.Tools }}
{{- . }}
{{ end }}
{{- end }}

{{ .Content }}<|eot_id|>{{ if $last }}<|start_header_id|>assistant<|end_header_id|>

{{ end }}
{{- else if eq .Role "assistant" }}<|start_header_id|>assistant<|end_header_id|>
{{- if .ToolCalls }}

{{- range .ToolCalls }}{"name": "{{ .Function.Name }}", "parameters": {{ .Function.Arguments }}}{{ end }}
{{- else }}

{{ .Content }}{{ if not $last }}<|eot_id|>{{ end }}
{{- end }}
{{- else if eq .Role "tool" }}<|start_header_id|>ipython<|end_header_id|>

{{ .Content }}<|eot_id|>{{ if $last }}<|start_header_id|>assistant<|end_header_id|>

{{ end }}
{{- end }}
{{- end }}"""
PARAMETER stop <|start_header_id|>
PARAMETER stop <|end_header_id|>
PARAMETER stop <|eot_id|>
    </code></pre>
    <li>Save the file and close it</li>
    <li>Run the following command in the same folder as the model and model card</li>
    <pre><code class="language-powershell">ollama create uncensored -f uncensored</code></pre>
    <li>You should see the following output</li>
    <pre><code class="language-powershell">Model "uncensored" has been added</code></pre>
    <li>You can now use the uncensored model in the chat</li>
    <li>Test this by running the following command:
        <pre><code class="language-powershell">ollama run uncensored</code></pre>
    </li>
</ol>

<h3>Step 8: Test the API</h3>
<p>You can now test the API by running the following command in PowerShell:</p>
<pre><code class="language-powershell">Invoke-RestMethod -Uri "http://localhost:11434/api/generate" -Method Post -Body '{"model": "uncensored", "prompt": "Why is the sky blue?", "stream":false}' -ContentType 'application/json'</code></pre>
<p>To ensure this API is working over the network, run the above command from a different machine on your network. Replace localhost with the IP address or name of the machine running Ollama.</p>

<h3>Step 9: Configure this application to use the Ollama API</h3>
<p><a href="?s1=Settings&s2=Configuration&s3=Detail&id=6">Click here</a> to configure this application to use the Ollama API. Fill out the endpoint information with the appropriate information. Eg. http://machine_name:11434/api/generate </p>

<h3>Step 10: Test the API</h3>
<p>Test the API by using the bellow button. If the API is working, you should see the response in the chat interface.</p>
<div class="row">
    <div class="col-md-2">
        <button class="btn btn-primary w-100" data-coreui-toggle="modal" data-coreui-target="#ai_chat_modal" onclick="open_chat('Is this thing working?', '')">Test Ollama API</button>
    </div>
</div>
<br />




<h3>Troubleshooting</h3>
<ul>
    <li>If you encounter any issues, ensure that you're running the latest version of Ollama.</li>
    <li>Check that your Windows version is up to date.</li>
    <li>If you receive any firewall warnings, make sure to allow Ollama through your firewall.</li>
    <li>For more detailed troubleshooting, visit the <a href="https://github.com/jmorganca/ollama/issues" target="_blank">Ollama GitHub Issues page</a>.</li>
</ul>


<h3>Note on Running Ollama Behind a Proxy</h3>
<p>If you are running Ollama behind a proxy, you may need to increase the timeouts to prevent connection issues. This is particularly important for larger language models or when processing longer prompts, as they may require more time to generate responses.</p>

<p>To increase the timeouts:</p>
<ol>
    <li>If you're using a reverse proxy like Nginx, adjust the proxy timeout settings in your Nginx configuration. For example:</li>
    <pre><code class="language-plaintext">proxy_read_timeout 3600s;
proxy_connect_timeout 3600s;
proxy_send_timeout 3600s;</code></pre>
    <li>If you're using a different proxy solution, consult its documentation for instructions on increasing timeout settings.</li>
    <li>You may also need to adjust the client-side timeout settings in your application that's making requests to Ollama.</li>
</ol>
<p>Remember to restart your proxy service after making these changes for them to take effect.</p>

<h3>Note on Running Ollama Behind a Nginx Proxy Manager</h3>
<p>Edit the proxy host. Go to settings -> Advanced. Place the following in the field marked "Custom Nginx Configuration".</p>
<pre><code class="language-plaintext">proxy_read_timeout 3600s;
proxy_connect_timeout 3600s;
proxy_send_timeout 3600s;</code></pre>


<?PHP
$common->print_template_card(null, 'end');
?>