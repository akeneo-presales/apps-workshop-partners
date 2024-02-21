# apps-workshop-partners

# Introduction and Goals
In that course we will initialize an Akeneo custom app that interacts with Google Vision API 
in order to extract labels from product images and assign these identified labels to the products informations in the PIM.

![diagram.png](diagram.png)


## Step 1 - Initialization : 

### Checkout the Demo App Skeleton :
In this step, you'll need to clone the Akeneo PIM Demo App Skeleton from the provided GitHub repository. 
This app skeleton will serve as the foundation for your custom app. 
Follow the steps below to get started:

### Prerequisites

Before you begin, ensure that you have the following installed on your development environment:

* Git client : https://git-scm.com/downloads
* Docker Desktop : https://www.docker.com/products/docker-desktop/

### Steps
1. Open your terminal or command prompt.
2. Navigate to the directory where you want to store your project.
3. Download the latest release of the Akeneo PIM Demo App Skeleton repository from that url https://github.com/akeneo-presales/app-skeleton
4. Unzip the archive
5. Start the docker stack by running the following command from the project root directory :
```
docker-compose up -d
```
6. Check that the app is running by opening the following url : http://localhost:8044

## Step 2: Connect the app to your PIM

**Requirements:**
- You have a [PIM developer sandbox](https://api.akeneo.com/apps/overview.html#app-developer-starter-kit)

**Steps:**
- Run Ngrok to open your local app to the internet, [install it](https://ngrok.com/download) and then run : ``ngrok http 8044`` (where 8044 is related to the DOCKER_PORT_HTTP constant value declared in the [Makefile](https://github.com/akeneo-presales/app-skeleton/blob/main/Makefile#L13) file), then you'll get a public ngrok url that you should use in the next steps
- [Register your app](https://api.akeneo.com/tutorials/how-to-get-your-app-token.html#step-3-declare-your-local-app-as-a-custom-app-in-your-sandbox-to-generate-credentials) to generates and receive the credentials, the activate url is : https://your-ngrok-url/ and the callback url is https://your-ngrok-url/callback
- Once the app registered in the PIM, you can [Connect to your app](https://api.akeneo.com/tutorials/how-to-get-your-app-token.html#step-4-run-your-local-app), then you will be prompted to register your PIM environment by providing the client id and the client secret you get on the previous step


## Step 3: Install Third-Party API Client and add Service Account configuration

In this step, you will install the Third-Party API client for addressing the Google Vision API, which will be used in your custom app. 
Follow the steps below to integrate the client into your project:

1. Open your terminal or command prompt.
2. Run the following command within your Docker container to install the Google AI Generative API client using Composer:

    ``bash
    docker exec -it presalesApp_web composer require google/cloud-vision
    ``

Now you have successfully installed the Third-Party API client for addressing the Google AI Generative API within your Dockerized Akeneo PIM app. Proceed to the next steps to configure and utilize this client within your custom app.

## Step 4: install Google Cloud Service Account
in order to request the Google Cloud Vision API, the Google Client should be authenticates, 
to do so we will use a Service account.

Copy the service account credentials json key into a service_account.json file at the root of the project.

## Few information about the PIM catalog Structure

All products have a packshot asset_collection attribute which handles the product images.

Also a product_labels text attribute has been also added, this field will receive the detected labels from the app. It's empty by default.


## Step 5 : Start coding a service class

In that step we will code a little Service class, GoogleVisionService to create in the Service Folder.
Here is the class diagram :

```
-----------------------------------------------------------------------
|                         GoogleVisionService                         |
-----------------------------------------------------------------------
| - clientFactory: PimApiClientFromTenantFactory                      |
-----------------------------------------------------------------------
| + GoogleVisionService(clientFactory: PimApiClientFromTenantFactory) |
| + detectLabelsOnProductImages(tenant: Tenant): void                 |
| - getLabelsForImage(imagePath: string): array                       |
-----------------------------------------------------------------------
```

The PimApiClientFromTenantFactory clientFactory attribute is injected through the constructor. It will help us later by giving us an API client based on the tenant.

The private method getLabelsForImage will receive an image path as argument and then it will call the Google Vision API to retrieve the labels.
To help you coding the method logic you can get inspiaration from what is presented here : https://cloud.google.com/vision/docs/samples/vision-label-detection?#vision_label_detection-php

The detectLabelsOnProductImages method will ask the clientFactory to give a PIM API Client based on the Tenant argument, 
then we will retreive products with an asset image (packshot not empty), and no labels (product_labels empty).
Then for each products you will get the asset image, storing it locally and then make a call to the getLabelsForImage method to retreive the labels.
Finnaly you will make a product update API request with the labels separated by a comma.


## Step 6: Update the product list page
From the product list page add a button on each product that triggers an Action (Ajax ?) which will retrieve the product image, storing it in a temp file, calls the CloudVision service method to retrieve tags and push the result back to the PIM.


## Step 7: Create a Commandline action

An action available from the command line can be triggered manually or automatically by a crontab configuration, it requires an access token to be able to discuss with the PIM


