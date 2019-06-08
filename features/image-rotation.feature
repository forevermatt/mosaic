Feature: Rotate images properly

  Scenario Outline: Importing and properly rotating images
    Given I have an image where the <side> should be up
    When I load the image
    Then the <side> should now be up
  
  Examples:
    | side       |
    | top        |
    | right side |
    | left side  |
