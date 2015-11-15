# Mosaic Maker #

## Plan ##

A. Get guide image.
B. Get source images.
C. Slice up guide image.
D. Match up each slice of the guide image with a different source image.
E. Assemble and save mosaic.

---

C.1. Count source images.
  2. Slice guide image into no more than that many slices.

---

D.1. Downsize the slice to 3 pixels by 3 pixels. Record the RGB values of the 
     pixels.
  2. Compare that with each of the source images.
  3. Make a note of which unused source image matches this slice best.

---

D.2.a. Downsize the source image to 3 pixels by 3 pixels. Record the RGB 
       values of the pixels.
    b. Calculate the absolute difference of slice's pixels' colors with this
       source image's pixels' colors.

---

E.1. Create a new empty image.
  2. Insert each of the source images according to the matches found.

---

Match accuracy (at a given resolution) = D[1] + D[2] + ... + D[number of pixels]

D = abs(R[a] - R[b]) + abs(G[a] - G[b]) + abs(B[a] - B[b])

Only accuracy calculations at matching resolutions give a meaningful comparison.

## Glossary ##

- *guide image*:
  The image that the mosaic is trying to mimic.
- *slice* (aka. guide image slice):
  A portion of the original guide image.
- *source images*:
  The images being used to recreate the guide image as a mosaic.
