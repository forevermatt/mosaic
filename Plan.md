A. Get guide image.
B. Get source images.
C. Slice up guide image.
D. Match up source images with slices of guide image.
E. Assemble and save mosaic.

---

C.1. Count source images.
  2. Slice guide image into no more than that many slices.

---

D.1. Get signature of each source image.
  2. For each slice, find the best match in the source images, not excluding any yet.

---

D.1.a. Downsize the slice to 1 pixel. Record color.
    b. If/when necessary, up to a finite limit, downsize the original slice to
       four (4) times as many pixels as last time. Record colors.

---

D.2.a. If the 1-pixel color color matches at least as well as any other source
       images so far, keep it. Forget any that don't match as well.
    b. If we have several equal matches, compare the next higher resolution.
       Repeat until we found a "best" match (up to a finite limit).
    c. Record how accurate a match it was.

---

E.1. Take the most accurate match we found and record it in our final list.
  2. Take the next most accurate match.  If that source image is available, 
     record it. If not, find a new best match for that slice from among the 
     available source images. Start this step (E.2) over.
  3. Repeat step E.2 until all the slices have a match.
  4. Assemble the mosaic from the data in our final list.

---

Match accuracy (at a given resolution) = D[1] + D[2] + ... + D[number of pixels]

D = abs(R[a] - R[b]) + abs(G[a] - G[b]) + abs(B[a] - B[b])

Only accuracy calculations at matching resolutions give a meaningful comparison.
