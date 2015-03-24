A. Get guide image.
B. Get source images.
C. Slice up guide image.
D. Match up source images with slices of guide image.
E. Assemble and save mosaic.

---

C.1. Count source images.
  2. Slice guide image into no more than that many slices.

---

D.1. For each slice, find the best match in the source images, not excluding
     any yet.
  2. Take the most accurate match we found and record it in our final list.
  3. Take the next most accurate match.  If that source image is available, 
     record it; if not, find a new best match for that slice from among the 
     available source images. Start this step (D.3) over.
  4. Repeat step D.3 until all the slices have a match.

---

D.1.a. Downsize the slice to 1 pixel. Record the RGB values of the pixel.
    b. Compare that with each of the source images.
    c. Make a note of which source image matche this slice best.

---

D.1.b.i. Downsize the source image to 1 pixel. Record the RGB values of the
         pixel.
     ii. Calculate the absolute difference of slice's pixel's color with this
         source image's pixel's color.

---

E.1. Create a new empty image.
  2. Insert each of the source images from our final list of best matches.

---

Match accuracy (at a given resolution) = D[1] + D[2] + ... + D[number of pixels]

D = abs(R[a] - R[b]) + abs(G[a] - G[b]) + abs(B[a] - B[b])

Only accuracy calculations at matching resolutions give a meaningful comparison.
