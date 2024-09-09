<?PHP
$common->print_template_card('Doctor API Endpoint', 'start');

?>
<h2>Setting up the FreeLawProject/Doctor Docker Container</h2>
<p>The FreeLawProject/Doctor container is a crucial component of our application, serving as a specialized API for processing and analyzing documents. It provides the necessary text extraction for PDFs and word documents.</p>

<p>This guide will walk you through the process of setting up the Doctor API endpoint using the FreeLawProject/Doctor Docker container.</p>
<p>This is part of the docker compose file and manual setup should no longer be necessary.</p>
<p>To test this API, go to the <a href="?s1=Settings&s2=Integrations">integrations page</a> and click the "Test Doctor Integration" button.</p>

<h3>Prerequisites</h3>
<ul>
    <li>Docker installed on your system</li>
    <li>Basic knowledge of Docker commands</li>
</ul>

<h3>Steps to Set Up</h3>

<ol>
    <li>
        <strong>Pull the Docker Image:</strong>
        <pre><code class="language-bash">docker pull freelawproject/doctor:latest</code></pre>
    </li>
    <li>
        <strong>Run the Docker Container:</strong>
        <pre><code class="language-bash">docker run -d -p 5050:5050 --name doctor freelawproject/doctor:latest</code></pre>
        <p>This command runs the container in detached mode (-d) and maps port 8000 of the container to port 8000 on your host machine.</p>
    </li>
    <li>
        <strong>Verify the Container is Running:</strong>
        <pre><code class="language-bash">docker ps</code></pre>
        <p>You should see the 'doctor' container in the list of running containers.</p>
    </li>
    <li>
        <strong>Access the API:</strong>
        <p>The Doctor API should now be accessible at <code>http://localhost:8000</code>.</p>
        <p>Replace localhost with the IP address or hostname of the machine running the container if you want to access it from outside the container.</p>
    </li>
</ol>

<h3>Configuration</h3>
<p>The Doctor API doesn't require additional configuration out of the box. However, you may need to adjust your application's settings to point to the correct Doctor API endpoint.</p>

<h3>Troubleshooting</h3>
<ul>
    <li>If the container fails to start, check the logs using: <code>docker logs doctor</code></li>
    <li>Ensure that port 8000 is not being used by another application on your system.</li>
    <li>If you need to stop the container, use: <code>docker stop doctor</code></li>
    <li>To remove the container, use: <code>docker rm doctor</code></li>
</ul>

<p>For more detailed information and advanced usage, please refer to the <a href="https://github.com/freelawproject/doctor" target="_blank">official FreeLawProject/Doctor GitHub repository</a>.</p>
<?PHP
$common->print_template_card(null, 'end');
?>