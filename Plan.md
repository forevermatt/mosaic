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

D.1.a. Get signature of the current slice.
    b. Compare it with each source image's signature and make a note of which
       one is the best match.

---


D.1.a.i. Downsize the slice to 1 pixel. Record the color.
     ii. If/when necessary (to find a more precise match), up to a finite
         limit, downsize the original slice to four (4) times as many pixels as
         last time. Record the color of each pixel.

---

D.1.b.i. If the 1-pixel color matches at least as well as any other source images
         so far, keep it. Forget any that don't match as well.
     ii. If we have several equal matches, compare the next higher resolution.
         Repeat until we found a "best" match (up to a finite limit, taking the
         first of our current "best" matches found if the limit is reached).
    iii. Record how accurate a match it was.

---

E.1. Create a new empty image.
  2. Insert each of the source images from our final list of best matches.

---

Match accuracy (at a given resolution) = D[1] + D[2] + ... + D[number of pixels]

D = abs(R[a] - R[b]) + abs(G[a] - G[b]) + abs(B[a] - B[b])

Only accuracy calculations at matching resolutions give a meaningful comparison.
