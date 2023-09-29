<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Service\ValidatorService;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class ProfileController
{
    private ValidatorService $validator;
    private bool $formVisible = false;

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private Messages       $flash
    )
    {
        $this->validator = new ValidatorService();
    }


    public function showProfile(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'profile.twig', [
            "email" => $_SESSION['email'],
            'signed_in' => true
        ]);
    }

    public function processProfileForm(Request $request, Response $response): Response
    {

        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        $uuid = uniqid(); // Generate a unique ID as the base name for the uploaded file

        // Set the target file name to the generated UUID with the file extension appended
        $target_file = $target_dir . $uuid . '.' . $imageFileType;

        $errors['image'] = "";
        $errors['ok'] = "";

        # The size of the image must be less than 1MB. OK
        # Only png and jpg images are allowed. OK
        # The image dimensions must be 400x400 (optionally, you can allow equal or less than 400x400). You can use this service to create example images. Also, be careful to not commit images to the remote repository. OK.
        # You need to generate a UUID for the image and save it using the generated UUID as the image name (plus extension).

        if (empty($_FILES['fileToUpload']['name'])) {
            // The file input field is empty
            $errors['image'] = "You have not selected an image.";
            $uploadOk = 0;
        } else {
            // Check if image file is a actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    $errors['image'] = "File is not an image.";
                    $uploadOk = 0;
                }
            }

            // Check if file already exists
            if (file_exists($target_file)) {
                $errors['image'] = "Sorry, file already exists.";
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 1000000) {
                $errors['image'] = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png") {
                $errors['image'] = "Sorry, only JPG, and PNG files are allowed.";
                $uploadOk = 0;
            }

            list($width, $height) = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            # echo "Width is" . $width;
            # echo "Height is" . $height;
            if ($width != 400 || $height != 400) {
                $errors['image'] = "Sorry, only 400x400 images are allowed.";
                $uploadOk = 0;
            }
        }
        
        // Check if $uploadOk is set to 0 by an error. If not, then upload.
        if ($uploadOk != 0) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $errors['ok'] = "The file has been uploaded.";
            }
        }
        

        return $this->twig->render($response, 'profile.twig', [
            "email" => $_SESSION['email'],
            "showForm" => false,
            "formErrors" => $errors,
            'signed_in' => true
        ]);
    }
}
