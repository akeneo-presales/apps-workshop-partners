# apps-workshop-partners

# Introduction and Goals
In that course we will initialize an Akeneo custom app that interacts with Google Vision API 
in order to extract labels from product images and assign these identified labels to the products informations in the PIM.

![diagram.png](diagram.png)

# Custom App Components

![image](https://github.com/akeneo-presales/apps-workshop-partners/assets/3144589/95fdb60a-3f4f-46a4-8047-371c498436f9)

## 1 - Initialization : 

### Checkout the Demo App Skeleton :
In this step, you'll have to download the latest Release from the Akeneo PIM Workshop App from the provided GitHub repository. 
This app will serve as the foundation for your custom app.
Follow the steps below to get started:

### Prerequisites

Before you begin, ensure that the following components are installed in your development environment:

* Docker Desktop : https://www.docker.com/products/docker-desktop/ : Once installed  => start it
* NGrok : [installation](https://ngrok.com/download)
* Archive manager : 7zip, Unarchiver

### Retrieve, install, and start the Custom App
1. Download the latest release of the Akeneo PIM Workshop App from that URL: https://github.com/akeneo-presales/apps-workshop-partners/releases/download/v1.4/WorkshopAppV1.4.tar.gz
2. Uncompress the archive
3. Open your terminal or command prompt.
4. Navigate to the directory where you have uncompressed the project archive.
5. Start the docker stack by running the following script from the project root directory :
    - For Linux / MacOS (Intel x86)
    ```
    ./start.sh
    ```
    - For Linux / MacOS (ARM64)
    ```
    ./start_arm64.sh
    ```
   - For Windows
    ```
    ./start.bat
    ```
6. Check that the app is running by opening the following url : http://localhost:8044

## 2 - Configure the OAuth Scopes required by the App

An Akeneo Custom App should declare the OAuth scopes needed for its execution.
Because the App will access different facets of the PIM we should ensure that the users are aware of it and approve or reject it during the app declaration process.
These scopes are declared In the Activate step.
Edit the ActivateAction Controller PHP class under the src/Controller/ActivateAction.php path to add the missing scopes for our app to work properly.

In our case we need to complete the list by adding the following scopes :
-  Read products,
-  Write products,
-  Read assets,
-  Read asset families,
-  Read catalog Structure,
-  Read Catalogs,
-  Write Catalogs,
-  Read locales and currencies,
-  Read channels

Check the documentation to find out the missing scopes: https://api.akeneo.com/apps/authentication-and-authorization.html#available-authorization-scopes

````php
final class ActivateAction extends AbstractController
{
    /* List the oAuth scopes required by your app
    In our case we need to complete the list by adding the following scopes :
    -  Read products,
    -  Write products,
    -  Read assets,
    -  Read asset families,
    -  Read catalog Structure,
    -  Read Catalogs,
    -  Write Catalogs,
    -  Read locales and currencies,
    -  Read channels
    See the documentation here to find out the missing scopes:
    https://api.akeneo.com/apps/authentication-and-authorization.html#available-authorization-scopes
*/
    private const OAUTH_SCOPES = [
        //add missing scopes here
        'openid',
        'profile',
        'email',
    ];

````

## 3 - Connect the App to your PIM

### Requirements:
- You have a [PIM developer sandbox](https://api.akeneo.com/apps/overview.html#app-developer-starter-kit)
- Your Custom APP is accessible from the PIM.

### Steps:
- Run [NGROK](https://ngrok.com/download) to obtain a temporary public URL for your local app, to do so run: ``ngrok http 8044``!
![ngrok.png](ngrok.png)
- [Register your app](https://api.akeneo.com/tutorials/how-to-get-your-app-token.html#step-3-declare-your-local-app-as-a-custom-app-in-your-sandbox-to-generate-credentials) to generates and receive the credentials, the activate URL is : https://your-ngrok-url/ and the callback URL is https://your-ngrok-url/callback
- Once the app is registered in the PIM, you can [Connect to your app](https://api.akeneo.com/tutorials/how-to-get-your-app-token.html#step-4-run-your-local-app)
- then you will be prompted to register your PIM environment by providing the Client ID and the Client Secret you get in the previous step

## 4: Create a Catalog for the App

Going back to the PIM we have to enable the catalog for our app.
Adding a filter on the criteria of our choice to retrieve only the products we want to address.

## 5: Install Third-Party API Client and add Service Account configuration

### Add the google/cloud-vision dependency

In this step, you will install the Third-Party API client for addressing the Google Vision API, which will be used in your custom app. 
Follow the steps below to integrate the client into your project:

1. Open your terminal or command prompt.
2. Run the following command within your Docker container to install the Google AI Generative API client using Composer:

    ```shell
    docker exec -it workshopApp_web composer require google/cloud-vision
    ```

Now you have successfully installed the Third-Party API client for addressing the Google AI Generative API within your Dockerized Akeneo PIM app. Proceed to the next steps to configure and utilize this client within your custom app.

### Add a Google Cloud Service Account configuration file
in order to request the Google Cloud Vision API, the Google Client should be authenticated, 
to do so we will use a Service account that has rights to request the Google Cloud Vision APIs.
We will provide you it's content for the time of the workshop.

Copy the service account credentials json key into a ***service_account.json*** file at the root of the project.

## 6 : Coding

In order to cover the use case presented in the introduction we will code a few things.
All that we will need to do will be centralized in a single Service Class : GoogleVisionService
the central method is detectLabelsOnProductImages, in that method we request the products through the catalog connection.
Foreach products that have their packshot asset collection described do the following :
### 6.1 Extract the image
in the **extractAssetImage** method implement the download of an asset content through the api
````php
private function extractAssetImage(AkeneoPimClientInterface $client, string $assetDataCode)
{
$mediaContent ='';

     /*
      * CODING
      * Extract the asset media datas (image binary data) from the ÀPI
      * store these data into the mediaContent var
      * See this documentation to find out how to do it
      * https://api.akeneo.com/php-client/resources.html#asset-media-file
      * and https://api.akeneo.com/php-client/resources.html#download-media-file
      */
     $mediaContent = '???'; //<-- binary data of the asset image

     $tempFile = tempnam('/tmp', 'assetGoogleVision');
     file_put_contents($tempFile, $mediaContent);

     return $tempFile;
}
````
### 6.2 Call the Google Vision Service to detect labels over the image
the method **getLabelsForImage** receives an image path as an argument. 
Code the little logic to call the Google Vision API to retrieve the labels and store them in the result array.
````php
 private function getLabelsForImage($imagePath)
    {
        $image = file_get_contents($this->projectDir.'/public/'.$imagePath);

        $result = [];

        /* NOW IT'S YOUR TIME TO CODE!!
        BE INSPIRED BY THIS GOOGLE DOCUMENTATION PAGE
        https://cloud.google.com/vision/docs/samples/vision-label-detection
        */
        $imageAnnotator = new ImageAnnotatorClient(['credentials'=>$this->projectDir.'/service_account.json']);

        /*
        END OF YOUR CODE
        */

        return $result;
    }
````

### 6.3 Update the product with the labels
 
 the Product attribute to update is the product_tags attribute, 
 we should use the implode function to concatenate the array of the detected labels, assign the string result to the en_US locale and ecommerce scope
 And then pushing the new product values by using the API client

````php

    private function updateProduct(AkeneoPimClientInterface $client, mixed $productUuid, array $labels, ?string $locale = 'en_US', ?string $scope = 'ecommerce')
    {

        $product = [
            'uuid' => $productUuid,
            'values' => [
                // update product_tags attribute
            ]
        ];

        /*
         * CODING
         * Update the product using its UUID with the tags that we retrieved from Google Vision API
         * See the documentation :
         * https://api.akeneo.com/php-client/resources.html#upsert-a-product-2
         */

        $response = '???';

        $this->checkUpsertResponse($response);
    }
````

## Information about the PIM catalog Structure

All products have a **packshot** asset_collection attribute which handles the product images.

Also, a **product_tags** textarea attribute has been also added, this field will receive the detected labels from the app.
