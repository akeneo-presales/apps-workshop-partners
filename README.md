# apps-workshop-partners

## Step 1 - Initialization : 

### Checkout the Demo App Skeleton :
In this step, you'll need to clone the Akeneo PIM Demo App Skeleton from the provided GitHub repository. This app skeleton will serve as the foundation for your custom app. Follow the steps below to get started:

### Prerequisites

Before you begin, ensure that you have the following installed on your development environment:

* Git
* PHP
* Composer

### Steps

1. Open your terminal or command prompt.
2. Navigate to the directory where you want to store your project.
3. Clone the Akeneo PIM Demo App Skeleton repository by running the following command:

    ``bash
    git clone https://github.com/akeneo-presales/app-skeleton.git
    ``


4. Change into the newly created directory:

    ``bash
    cd app-skeleton
    ``


5. Install the project dependencies and run the app using the provided Makefile command:

    ``bash
    make build
    ``

Now you have successfully cloned the Akeneo PIM Demo App Skeleton and installed the project dependencies using the provided Makefile command. Move on to the next step for further configuration and customization.


## Step 2: Install Third-Party API Client

In this step, you will install the Third-Party API client for addressing the Google AI Generative API, which will be used in your custom app. Follow the steps below to integrate the client into your project:


### Prerequisites

* Ensure that you have completed Step 1 and have the Akeneo PIM Demo App Skeleton set up and running.
* Make sure you are running your app within a Docker container.

### Steps

1. Open your terminal or command prompt.
2. Run the following command within your Docker container to install the Google AI Generative API client using Composer:

    ``bash
    docker exec -it presalesApp_web composer require google/cloud-ai-platform
    ``


1. Wait for the Composer to download and install the required dependencies.
2. Once the installation is complete, verify that the Google AI Generative API client is added to your composer.json file.

Now you have successfully installed the Third-Party API client for addressing the Google AI Generative API within your Dockerized Akeneo PIM app. Proceed to the next steps to configure and utilize this client within your custom app.


## Step 3: Enable Vertex AI API on Google Cloud Project

In this step, you will enable the Vertex AI API on your Google Cloud Project. If you don't have a Google Cloud project, you can create a new one. Follow the steps below to enable the API:

### Steps

1. Open your web browser and go to the Google Cloud Console.
2. If you don't have a Google Cloud project, create a new one by navigating to https://console.cloud.google.com/projectcreate. Follow the prompts to set up a new project.
3. Once your project is created or if you already have a project, navigate to the API Library by clicking on the hamburger menu (☰) and selecting "APIs & Services" > "Library."
4. In the API Library, use the search bar to look for "Vertex AI API."
5. From the search results, select the "Vertex AI API."
6. Click on the "Enable" button to enable the Vertex AI API for your project.
7. Wait for the API to be enabled. You will receive a notification once the process is complete.

Now you have successfully enabled the Vertex AI API on your Google Cloud Project. Proceed to the next steps to configure and integrate the Google AI Generative API client within your Akeneo PIM custom app.


## Step 4: Create a Google Cloud Service Account

In this step, you will create a Google Cloud service account, which will allow your Akeneo PIM custom app to authenticate and use the Vertex AI API capabilities. Follow the steps below to create the service account and retrieve a JSON key file:

### Steps

1. Open your web browser and go to the Google Cloud Console IAM & Admin page.
2. Ensure that you have selected the correct project from the project dropdown at the top of the page.
3. Click on the "Create Service Account" button.
4. In the "Service account name" field, enter a name for your service account.
5. In the "Role" field, select the appropriate role for your service account. For Vertex AI API access, you can use the "Vertex AI Admin" role.
6. Check the box for "Furnish a new private key."
7. Choose the key type as "JSON."
8. Click on the "Create" button.
9. The JSON key file will be downloaded to your local machine.
10. Move the downloaded JSON key file to the root of your Akeneo PIM custom app project and rename it to service_account.json.

Now you have successfully created a Google Cloud service account and obtained the JSON key file. The service_account.json file will be used for authentication with Google Cloud and accessing the Vertex AI API capabilities in your custom app. Proceed to the next steps to configure your app accordingly.
