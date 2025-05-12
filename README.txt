I asked different AIs to do the following

```
Write a PHP script, using OOP, that simulates the following given a number of sets:

Steps:
1. Soap Wash. 15 minutes
2. Spin Dry. 3 minutes
3. Water Wash. 10 minutes
4. Hang Dry. 5 minutes

Rules:
a. Steps are done sequentially for every set.
b. Only one set can be processed in every step.
c. Transition from one step to another takes 1 minute. No other steps can start while a transition is on-going.
d. Each step has to be completed when started without interruption.
e. Water Wash can be done while the other steps are on-going, except while a transition is on-going.
f. Soap Wash can be started for the next set only when Spin Dry or Hang Dry is about to start.
g. Steps can be started in parallel except for Spin Dry and Hang Dry. Hang Dry takes higher priority than Spin Dry.

The script provides a timeline in minutes, each step is started and finished for every set.
The script can also determine how many sets can be completed in a given number of minutes.
```

Each of the AI's response are stored on their own directory.

**TLDR**: ChatGPT's v2 best time for 5 sets is 124 minutes while Qwen's at 120 minutes
